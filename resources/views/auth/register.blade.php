<x-headeradmin>
    <form method="POST" action="{{ route('admin.register.store') }}">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="mt-4">
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input id="username" class="block mt-1 w-full" type="username" name="username" :value="old('username')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        {{-- nip --}}
        <div>
            <x-input-label for="nip" :value="__('Nip')" />
            <x-text-input id="nip" class="block mt-1 w-full" type="text" name="nip" :value="old('nip')" required autofocus autocomplete="nip" />
            <x-input-error :messages="$errors->get('nip')" class="mt-2" />
        </div>
        {{-- jabatan --}}
        <div>
            <x-input-label for="jabatan" :value="__('Jabatan')" />
            <x-text-input id="jabatan" class="block mt-1 w-full" type="text" name="jabatan" :value="old('jabatan')" required autofocus autocomplete="jabatan" />
            <x-input-error :messages="$errors->get('jabatan')" class="mt-2" />
        </div>

        {{-- nama pegawai (pre-filled from name) --}}
        <div class="mt-4">
            <x-input-label for="nama_pegawai" :value="__('Nama Pegawai')" />
            <x-text-input id="nama_pegawai" class="block mt-1 w-full" type="text" name="nama_pegawai" :value="old('nama_pegawai', old('name'))" required autocomplete="nama_pegawai" />
            <x-input-error :messages="$errors->get('nama_pegawai')" class="mt-2" />
        </div>

        {{-- divisi select --}}
        <div class="mt-4">
            <x-input-label for="divisi_id" :value="__('Divisi')" />
            <select id="divisi_id" name="divisi_id" class="block mt-1 w-full" required>
                <option value="">-- Pilih Divisi --</option>
                @foreach($divisi as $d)
                    <option value="{{ $d->id }}" {{ old('divisi_id') == $d->id ? 'selected' : '' }}>{{ $d->nama_divisi }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('divisi_id')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</x-headeradmin>
