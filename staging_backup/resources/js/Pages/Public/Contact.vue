<template>
  <Head title="Kontak Kantor & Lokasi — Disdukcapil Dompu" />

  <PublicLayout>
    <div class="space-y-8 text-left max-w-7xl mx-auto">
      <!-- Title section -->
      <div>
        <span class="px-3 py-1 bg-primary-50 dark:bg-zinc-800 text-primary-600 dark:text-primary-400 text-xs font-black uppercase tracking-wider rounded-lg border border-primary-200 dark:border-zinc-700">
          Kontak & Lokasi Kantor
        </span>
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-zinc-50 tracking-tight mt-2">
          Hubungi Kami & Peta Lokasi
        </h1>
        <p class="text-xs text-gray-500 dark:text-zinc-400 mt-1">
          Kunjungi kantor pelayanan kami di Kabupaten Dompu atau lakukan konsultasi langsung via WhatsApp Center & Media Sosial Resmi.
        </p>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Contact Information Panel (Col Span 4) -->
        <div class="lg:col-span-4 space-y-6">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm space-y-4">
            <h3 class="text-xs font-extrabold uppercase tracking-wider text-primary-600 dark:text-primary-400">Informasi Pelayanan</h3>
            
            <div class="space-y-4">
              <!-- Address -->
              <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest font-mono">Alamat Kantor</p>
                <p class="text-xs font-bold text-gray-800 dark:text-zinc-200 mt-1 leading-relaxed">
                  {{ contactInfo.address }}
                </p>
              </div>

              <!-- Email -->
              <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest font-mono">Alamat Email Resmi</p>
                <p class="text-xs font-bold text-gray-800 dark:text-zinc-200 mt-1 leading-relaxed">
                  {{ contactInfo.email }}
                </p>
              </div>

              <!-- Work Hours -->
              <div>
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest font-mono">Jam Operasional Pelayanan</p>
                <p class="text-xs font-bold text-gray-800 dark:text-zinc-200 mt-1 leading-relaxed">
                  {{ contactInfo.work_hours }}
                </p>
              </div>

              <!-- Social Media Links -->
              <div class="pt-2 border-t border-gray-100 dark:border-zinc-800">
                <p class="text-[10px] font-black text-gray-400 uppercase tracking-widest font-mono mb-2">Media Sosial Resmi</p>
                <div class="flex flex-col gap-2">
                  <a 
                    v-if="contactInfo.social_instagram"
                    :href="contactInfo.social_instagram"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-pink-500/10 to-rose-500/10 hover:from-pink-500/20 hover:to-rose-500/20 text-pink-600 dark:text-pink-400 text-xs font-bold rounded-xl border border-pink-500/20 transition"
                  >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    <span>Instagram Resmi</span>
                  </a>

                  <a 
                    v-if="contactInfo.social_facebook"
                    :href="contactInfo.social_facebook"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-2 px-3 py-2 bg-gradient-to-r from-blue-500/10 to-indigo-500/10 hover:from-blue-500/20 hover:to-indigo-500/20 text-blue-600 dark:text-blue-400 text-xs font-bold rounded-xl border border-blue-500/20 transition"
                  >
                    <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M9 8H6v4h3v12h5V12h3.642L18 8h-4V6.333C14 5.374 14.5 5 15.5 5H18V0h-3.808C10.592 0 9 1.583 9 4.615V8z"/></svg>
                    <span>Facebook Resmi</span>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- WhatsApp Chat Trigger CTA -->
          <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white rounded-3xl p-6 shadow-sm flex flex-col justify-between space-y-4">
            <div class="space-y-1">
              <span class="px-2.5 py-0.5 bg-white/20 text-[10px] font-black uppercase rounded-md tracking-wider inline-block">
                Fast Response
              </span>
              <h3 class="text-base font-extrabold tracking-tight mt-2">WhatsApp Help Center</h3>
              <p class="text-xs text-white/90 leading-relaxed font-medium">
                Hubungi petugas konsultasi berkas instan tanpa perlu datang mengantri di loket kantor.
              </p>
            </div>
            <a 
              :href="`https://wa.me/${contactInfo.whatsapp}`" 
              target="_blank"
              class="w-full text-center py-3 bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-black rounded-xl transition shadow-md shadow-amber-500/20 active:scale-95"
            >
              Mulai Chat WhatsApp Center →
            </a>
          </div>
        </div>

        <!-- Google Maps / Leaflet Map Frame (Col Span 8) -->
        <div class="lg:col-span-8 space-y-4">
          <div class="bg-white dark:bg-zinc-900 border border-gray-100 dark:border-zinc-800 rounded-3xl p-6 shadow-sm flex flex-col space-y-4">
            <div class="flex items-center justify-between flex-wrap gap-2">
              <h3 class="text-xs font-extrabold uppercase tracking-wider text-primary-600 dark:text-primary-400">
                Peta Presisi Lokasi Kantor Disdukcapil
              </h3>

              <a 
                :href="`https://www.google.com/maps/search/?api=1&query=${contactInfo.map_lat},${contactInfo.map_lng}`" 
                target="_blank"
                rel="noopener noreferrer"
                class="px-3.5 py-1.5 bg-primary-50 dark:bg-zinc-800 hover:bg-primary-100 text-primary-600 dark:text-primary-400 text-xs font-bold rounded-xl border border-primary-200 dark:border-zinc-700 transition flex items-center gap-1.5"
              >
                <span>Buka di Google Maps</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
              </a>
            </div>

            <!-- Leaflet Map Container -->
            <div class="h-[30rem] w-full relative rounded-2xl overflow-hidden border border-gray-100 dark:border-zinc-800 z-10 shadow-inner">
              <div id="contact-map" class="absolute inset-0 bg-zinc-100 dark:bg-zinc-950"></div>
            </div>

            <div class="text-[11px] text-gray-500 dark:text-zinc-400 font-medium flex items-center justify-between pt-1">
              <span>📍 Koordinat Presisi: {{ contactInfo.map_lat }}, {{ contactInfo.map_lng }}</span>
              <span>{{ contactInfo.address }}</span>
            </div>
          </div>
        </div>

      </div>
    </div>
  </PublicLayout>
</template>

<script setup>
import { onMounted, onBeforeUnmount } from 'vue';
import { Head } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

const props = defineProps({
  contactInfo: Object,
});

let map = null;

onMounted(() => {
  const lat = parseFloat(props.contactInfo.map_lat || '-8.536780');
  const lng = parseFloat(props.contactInfo.map_lng || '118.461295');

  // Initialize map with precision zoom level 16
  map = L.map('contact-map').setView([lat, lng], 16);

  // High resolution OpenStreetMap tile layer
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom: 19,
    attribution: '&copy; OpenStreetMap contributors'
  }).addTo(map);

  // Custom SVG Marker Icon
  const customIcon = L.divIcon({
    className: 'custom-leaflet-pin',
    html: `
      <div style="
        position: relative;
        width: 42px;
        height: 42px;
        background: #0274cb;
        border: 3px solid #ffffff;
        border-radius: 50% 50% 50% 0;
        transform: rotate(-45deg);
        box-shadow: 0 4px 12px rgba(2, 116, 203, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
      ">
        <div style="
          width: 14px;
          height: 14px;
          background: #f59e0b;
          border-radius: 50%;
          transform: rotate(45deg);
        "></div>
      </div>
    `,
    iconSize: [42, 42],
    iconAnchor: [21, 42],
    popupAnchor: [0, -42]
  });

  // Add Marker with Popup
  L.marker([lat, lng], { icon: customIcon })
    .addTo(map)
    .bindPopup(`
      <div style="font-family: inherit; font-size: 12px; padding: 4px; line-height: 1.4;">
        <strong style="color: #0274cb; font-size: 13px; display: block; margin-bottom: 4px;">
          Dinas Kependudukan & Pencatatan Sipil Kabupaten Dompu
        </strong>
        <span>${props.contactInfo.address}</span>
      </div>
    `)
    .openPopup();
});

onBeforeUnmount(() => {
  if (map) {
    map.remove();
  }
});
</script>

<style>
.leaflet-container {
  font-family: inherit;
}
.custom-leaflet-pin {
  background: transparent;
  border: none;
}
</style>
