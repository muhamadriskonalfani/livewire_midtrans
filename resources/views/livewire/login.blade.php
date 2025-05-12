<div>
    <div class="d-flex justify-content-center">
        <div class="pt-4" style="min-width: 350px; max-width: 400px; width: 100%;">
            <div class="">
                <h3 class="text-center mb-4"><i class="fas fa-user-plus me-2"></i>Login</h3>
                <form wire:submit.prevent="login">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" class="form-control" placeholder="Masukkan email" wire:model.defer="email" required>
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input id="password" type="password" class="form-control" placeholder="Masukkan password" wire:model.defer="password" required>
                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <button type="submit" class="btn btn-warning w-100">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
