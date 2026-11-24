<?php

use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts.public'), Title('Daftar Alumni Bercerita')] class extends Component {};
?>

<main class="min-h-screen bg-slate-50 py-8 sm:py-10">
    <div class="mx-auto max-w-2xl px-4">
        <a href="{{ route('home') }}" class="text-sm font-semibold text-primary-dark hover:text-primary">← Alumni Bercerita</a>
        <h1 class="mt-4 text-3xl font-bold text-slate-900">Pendaftaran karya</h1>
        <p class="mt-2 text-slate-600">Lengkapi data dan unggah video karya Anda.</p>

        <form id="alumni-registration-form" x-data="alumniVideoRegistration()" @submit.prevent="submit" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-6">
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input name="nama_lengkap" label="Nama lengkap" required />
                <flux:input name="tahun_kelulusan" label="Tahun kelulusan" type="number" required />
                <flux:input name="whatsapp" label="Nomor WhatsApp" required />
                <flux:input name="domisili" label="Domisili" required />
            </div>
            <flux:select name="status" label="Status" required><option value="">Pilih status</option><option>Pelajar</option><option>Mahasiswa</option><option>Bekerja</option><option>Lainnya</option></flux:select>
            <flux:select name="platform_sosial" label="Platform" required><option value="">Pilih platform</option><option value="instagram">Instagram</option><option value="tiktok">TikTok</option><option value="keduanya">Instagram & TikTok</option></flux:select>
            <div class="grid gap-4 sm:grid-cols-2">
                <flux:input name="username_instagram" label="Username Instagram (opsional)" />
                <flux:input name="link_instagram" label="Link Instagram (opsional)" type="url" />
                <flux:input name="username_tiktok" label="Username TikTok (opsional)" />
                <flux:input name="link_tiktok" label="Link TikTok (opsional)" type="url" />
            </div>
            <div class="space-y-2">
                <label for="alumni-video" class="text-sm font-medium text-slate-700">Video asli MP4/MOV (maks. 100 MB)</label>
                <input id="alumni-video" x-ref="video" name="video" type="file" accept="video/mp4,video/quicktime,.mp4,.mov" required class="block w-full rounded-lg border border-slate-500 bg-white p-2 text-sm text-slate-700 file:mr-3 file:rounded-md file:border-0 file:bg-primary-light/20 file:px-3 file:py-1.5 file:font-medium file:text-primary-dark hover:border-slate-600 focus:border-primary-dark focus:ring-2 focus:ring-primary-dark/30" :disabled="loading" />
                <p class="text-sm text-slate-600">Video langsung dikirim dari browser ke penyimpanan privat dan tidak melalui server aplikasi.</p>
            </div>
            <flux:checkbox name="keaslian" value="1" label="Saya menyatakan karya ini asli." required />
            <flux:checkbox name="persetujuan_publikasi" value="1" label="Saya menyetujui publikasi karya." required />
            <flux:checkbox name="patuh_ketentuan" value="1" label="Saya mematuhi ketentuan program." required />
            <div x-show="loading" x-cloak class="space-y-2 rounded-xl border border-primary-light bg-primary-light/15 p-4 text-sm text-slate-700">
                <div class="flex items-center justify-between gap-4"><span x-text="status"></span><span class="font-semibold" x-text="progress + '%'"></span></div>
                <div class="h-2 overflow-hidden rounded-full bg-primary-light/30"><div class="h-full bg-primary-dark transition-[width] duration-200" :style="`width: ${progress}%`"></div></div>
            </div>
            <p x-show="error" x-cloak class="rounded-lg bg-red-50 p-3 text-sm text-red-700" x-text="error"></p>
            <div class="flex flex-wrap gap-3"><flux:button type="submit" variant="primary" x-bind:disabled="loading">Kirim pendaftaran</flux:button><flux:button type="button" variant="ghost" x-show="loading" @click="cancel" x-bind:disabled="cancelling">Batalkan upload</flux:button></div>
        </form>
    </div>

    @script
        <script>
            Alpine.data('alumniVideoRegistration', () => ({
                loading: false, cancelling: false, progress: 0, status: '', error: '', xhr: null, upload: null,
                async submit() {
                    const form = document.getElementById('alumni-registration-form'); const file = this.$refs.video.files[0]; this.error = '';
                    if (!form.reportValidity() || !file) return;
                    if (!this.isSupportedVideo(file) || !await this.hasExpectedFileSignature(file)) { this.error = 'Pilih video MP4 atau MOV yang valid.'; return; }
                    this.loading = true; this.progress = 0;
                    try {
                        this.status = 'Mempersiapkan upload…';
                        this.upload = await this.requestJson(@js(route('alumni.registration.presign')), { file_name: file.name, content_type: file.type, size: file.size });
                        this.status = 'Mengunggah video…'; await this.uploadToS3(this.upload, file);
                        this.status = 'Memverifikasi video…';
                        const registration = Object.fromEntries(new FormData(form).entries()); delete registration.video; registration.upload_token = this.upload.upload_token; registration.object_key = this.upload.object_key;
                        this.status = 'Menyimpan pendaftaran…'; const result = await this.requestJson(@js(route('alumni.registration.confirm')), registration); window.location.assign(result.redirect_url);
                    } catch (error) {
                        if (this.upload) await this.cleanup();
                        this.error = error.message || 'Pendaftaran tidak dapat diproses.'; this.loading = false; this.progress = 0; this.status = ''; this.upload = null;
                    }
                },
                isSupportedVideo(file) {
                    const extension = file.name.split('.').pop()?.toLowerCase();
                    return file.size > 0 && file.size <= 104857600 && ['mp4', 'mov'].includes(extension) && ({ mp4: 'video/mp4', mov: 'video/quicktime' })[extension] === file.type;
                },
                async hasExpectedFileSignature(file) {
                    const bytes = new Uint8Array(await file.slice(0, 4096).arrayBuffer()); const extension = file.name.split('.').pop()?.toLowerCase();
                    for (let offset = 0; offset + 16 <= bytes.length;) {
                        const size = (bytes[offset] << 24) | (bytes[offset + 1] << 16) | (bytes[offset + 2] << 8) | bytes[offset + 3]; const type = String.fromCharCode(...bytes.slice(offset + 4, offset + 8));
                        if (type === 'ftyp') { const brand = String.fromCharCode(...bytes.slice(offset + 8, offset + 12)); return extension === 'mov' ? brand === 'qt  ' : ['isom', 'iso2', 'avc1', 'mp41', 'mp42', 'M4V ', 'dash', 'MSNV'].includes(brand); }
                        if (size < 8) return false; offset += size;
                    }
                    return false;
                },
                requestJson(url, body) {
                    return fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) }, body: JSON.stringify(body) }).then(async response => {
                        const payload = await response.json(); if (!response.ok) throw new Error(payload.errors ? Object.values(payload.errors).flat().join(' ') : (payload.message || 'Permintaan gagal.')); return payload;
                    });
                },
                uploadToS3(upload, file) {
                    return new Promise((resolve, reject) => {
                        this.xhr = new XMLHttpRequest(); this.xhr.open('PUT', upload.upload_url, true); this.xhr.timeout = 300000;
                        Object.entries(upload.headers).forEach(([name, value]) => this.xhr.setRequestHeader(name, value));
                        this.xhr.upload.onprogress = event => { if (event.lengthComputable) this.progress = Math.round((event.loaded / event.total) * 100); };
                        this.xhr.onload = () => this.xhr.status >= 200 && this.xhr.status < 300 ? (this.progress = 100, resolve()) : reject(new Error(`Upload ke penyimpanan gagal (HTTP ${this.xhr.status}).`));
                        this.xhr.onerror = () => reject(new Error('Koneksi langsung ke penyimpanan gagal.')); this.xhr.ontimeout = () => reject(new Error('Waktu upload habis. Silakan coba lagi.')); this.xhr.onabort = () => reject(new Error('Upload video dibatalkan.')); this.xhr.send(file);
                    });
                },
                async cancel() { this.cancelling = true; this.xhr?.abort(); await this.cleanup(); this.loading = false; this.cancelling = false; this.progress = 0; this.status = ''; this.upload = null; },
                async cleanup() {
                    if (!this.upload) return;
                    await fetch(@js(route('alumni.registration.cancel')), { method: 'DELETE', credentials: 'same-origin', keepalive: true, headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': @js(csrf_token()) }, body: JSON.stringify({ upload_token: this.upload.upload_token, object_key: this.upload.object_key }) });
                },
            }));
        </script>
    @endscript
</main>
