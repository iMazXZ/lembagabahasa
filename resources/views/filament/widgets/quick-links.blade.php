<x-filament-widgets::widget>
  <x-filament::section>

    @php
      $user = filament()->auth()->user();
    @endphp
    
    @role('tutor')
      <div
        class="mb-4 rounded-lg border border-green-300/60 bg-green-50 px-4 py-3 text-sm text-green-800
               dark:border-green-600/60 dark:bg-green-500/10 dark:text-green-400">
        <div class="flex items-start gap-2">
          <x-filament::icon icon="heroicon-o-academic-cap" class="h-5 w-5 mt-0.5"/>
          <p>
            Hai, <strong>{{ $user->name }}</strong> 👋 — kamu login sebagai
            <span class="font-semibold">Tutor</span>. Gunakan tautan cepat di bawah untuk
            mengelola mahasiswa binaan, connect code, dan memantau pengerjaan quiz.
          </p>
        </div>
      </div>
    @endrole

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
        @php
          $pendingSurat = \App\Models\EptSubmission::where('status', 'pending')->count();
          $pendingTerjemah = \App\Models\Penerjemahan::where('status', 'Menunggu')->count();
        @endphp

        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.suratrekomendasi.index') }}"
            size="lg"
            :color="$pendingSurat > 0 ? 'danger' : 'success'"
            icon="heroicon-o-document-check"
            :badge="$pendingSurat ?: null"
            class="w-full sm:w-auto"
        >
          Pengajuan Surat Rekomendasi
        </x-filament::button>

        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.penerjemahan.index') }}"
            size="lg"
            :color="$pendingTerjemah > 0 ? 'danger' : 'success'"
            icon="heroicon-o-language"
            :badge="$pendingTerjemah ?: null"
            class="w-full sm:w-auto"
        >
          Penerjemahan Abstrak
        </x-filament::button>

        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.users.index') }}"
            size="lg"
            color="success"
            icon="heroicon-o-users"
            class="w-full sm:w-auto"
        >
          Data Pendaftar
        </x-filament::button>

        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.basic-listening-connect-codes.index') }}"
            size="lg"
            color="success"
            icon="heroicon-o-link"
            class="w-full sm:w-auto"
        >
          Connect Code
        </x-filament::button>

        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.basic-listening-attempts.index') }}"
            size="lg"
            color="success"
            icon="heroicon-o-clipboard-document-check"
            class="w-full sm:w-auto"
        >
          Data Quiz Dikerjakan
        </x-filament::button>

        <x-filament::button
            tag="a"
            href="{{ route('filament.admin.resources.shield.roles.index') }}"
            size="lg"
            color="success"
            icon="heroicon-o-shield-check"
            class="w-full sm:w-auto"
        >
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
        
        <x-filament::button tag="a" href="{{ route('filament.admin.resources.basic-listening-connect-codes.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-link">
          Connect Code
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('filament.admin.resources.basic-listening-attempts.index') }}" size="lg" color="success"
            class="w-full sm:w-auto" icon="heroicon-o-clipboard-document-check">
          Data Quiz Dikerjakan
        </x-filament::button>

        <x-filament::button tag="a" href="{{ route('front.home') }}" size="lg" color="primary"
            class="w-full sm:w-auto" icon="heroicon-o-home">
          Halaman Utama
        </x-filament::button>
      @endrole


      {{-- Fallback bila tidak punya salah satu role di atas --}}
      @unlessrole('pendaftar|Admin|tutor')
        <x-filament::button tag="a" href="{{ route('front.home') }}" size="lg" color="primary"
            class="w-full sm:w-auto" icon="heroicon-o-home">
          Halaman Utama
        </x-filament::button>
      @endunlessrole
    </div>
  </x-filament::section>
</x-filament-widgets::widget>
