<x-filament-widgets::widget>
  <x-filament::section>
    @php
      $user = filament()->auth()->user();
    @endphp

    <div class="flex flex-wrap gap-3">
      {{-- =========================
           Role: pendaftar
           ========================= --}}
      @role('pendaftar')

        <x-filament::button tag="a" href="{{ url('/#berita') }}" size="lg" color="warning"
            class="w-full sm:w-auto" icon="heroicon-o-arrow-down-circle">
          Cek Jadwal & Nilai EPT
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('bl.index') }}" size="lg" color="warning"
            class="w-full sm:w-auto" icon="heroicon-o-musical-note">
          Basic Listening
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('bl.history') }}" size="lg" color="warning"
            class="w-full sm:w-auto" icon="heroicon-o-clock">
          History Basic Listening
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('verification.index') }}" size="lg" color="warning"
            class="w-full sm:w-auto" icon="heroicon-o-check-badge">
          Verifikasi Dokumen
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('front.home') }}" size="lg" color="primary"
            class="w-full sm:w-auto" icon="heroicon-o-home">
          Halaman Utama
        </x-filament::button>
      @endrole


      {{-- =========================
           Role: Admin
           ========================= --}}
      @role('Admin')
        <x-filament::button tag="a" href="{{ route('filament.admin.resources.suratrekomendasi.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-document-check">
          Pengajuan Surat Rekomendasi
        </x-filament::button>

        {{-- Jika heroicon-o-language tidak tersedia di versi kamu, ganti ke heroicon-o-globe-alt --}}
        <x-filament::button tag="a" href="{{ route('filament.admin.resources.penerjemahan.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-language">
          Penerjemahan Abstrak
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('filament.admin.resources.users.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-users">
          Data Pendaftar
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('filament.admin.resources.basic-listening-connect-codes.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-link">
          Connect Code
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('filament.admin.resources.basic-listening-attempts.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-clipboard-document-check">
          Data Quiz Dikerjakan
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('filament.admin.resources.shield.roles.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-shield-check">
          Pengaturan Role & Permission
        </x-filament::button>
      @endrole


      {{-- =========================
           Role: tutor
           ========================= --}}
      @role('tutor')
        <x-filament::button tag="a" href="{{ route('filament.admin.pages.tutor-mahasiswa') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-user-group">
          Data Mahasiswa Diampu
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('filament.admin.resources.users.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-users">
          Data Pendaftar
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('filament.admin.resources.basic-listening-connect-codes.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-link">
          Connect Code
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('filament.admin.resources.basic-listening-attempts.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-clipboard-document-check">
          Data Quiz Dikerjakan
        </x-filiment::button>

        <x-filament::button tag="a" href="{{ route('front.home') }}" size="lg" color="primary"
            class="w-full sm:w-auto" icon="heroicon-o-home">
          Halaman Utama
        </x-filament::button>
      @endrole


      {{-- Opsional: fallback jika user tidak punya salah satu role di atas --}}
      @unlessrole('pendaftar|Admin|tutor')
        <x-filament::button tag="a" href="{{ route('front.home') }}" size="lg" color="primary"
            class="w-full sm:w-auto" icon="heroicon-o-home">
          Halaman Utama
        </x-filament::button>
      @endunlessrole
    </div>
  </x-filament::section>
</x-filament-widgets::widget>
