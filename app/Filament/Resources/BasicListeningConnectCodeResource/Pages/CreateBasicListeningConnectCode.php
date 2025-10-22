<?php

namespace App\Filament\Resources\BasicListeningConnectCodeResource\Pages;

use App\Filament\Resources\BasicListeningConnectCodeResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

class CreateBasicListeningConnectCode extends CreateRecord
{
    protected static string $resource = BasicListeningConnectCodeResource::class;

    protected function makeCodeHint(string $code): string
    {
        $code = trim($code);
        $len  = mb_strlen($code);
        if ($len <= 4) {
            return mb_substr($code, 0, 1) . '••' . mb_substr($code, -1);
        }
        return mb_substr($code, 0, 2) . '•••' . mb_substr($code, -2);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $plain = data_get($data, 'plain_code'); // sekarang tersedia

        if (!$plain) {
            throw ValidationException::withMessages([
                'plain_code' => 'Kolom "Plain Code" wajib diisi.',
            ]);
        }

        $plain = trim($plain);
        $data['code_hash'] = hash('sha256', $plain);
        $data['code_hint'] = $this->makeCodeHint($plain);

        // parse rules JSON jika perlu
        if (!empty($data['rules']) && is_string($data['rules'])) {
            $decoded = json_decode($data['rules'], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data['rules'] = $decoded;
            }
        }

        unset($data['plain_code']); // jangan simpan plaintext
        return $data;
    }
}
