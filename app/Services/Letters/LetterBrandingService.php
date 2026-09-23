<?php

namespace App\Services\Letters;

use App\Models\LetterBrandingSetting;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LetterBrandingService
{
    private const DEFAULT_SIGNATURE = 'images/letters/sign_dgs.png';
    private const DEFAULT_STAMP = 'images/letters/stamp.png';
    private const UPLOAD_DIRECTORY = 'uploads/letter-branding';

    /**
     * Return the effective assets used by every backend-generated letter.
     *
     * The database is intentionally optional here. This keeps the PDF renderer
     * able to fall back to the bundled government assets during deployment
     * before the branding migration has been applied.
     */
    public function effectiveAssets(): array
    {
        $setting = $this->currentSetting();
        $signaturePath = $setting?->signature_path ?: self::DEFAULT_SIGNATURE;
        $stampPath = $setting?->stamp_path ?: self::DEFAULT_STAMP;

        return [
            'signatureData' => $this->dataUri($signaturePath),
            'stampData' => $this->dataUri($stampPath),
            'signaturePath' => $signaturePath,
            'stampPath' => $stampPath,
        ];
    }

    public function summary(): array
    {
        $setting = $this->currentSetting();
        $assets = $this->effectiveAssets();

        return [
            'signature_path' => $assets['signaturePath'],
            'stamp_path' => $assets['stampPath'],
            'signature_url' => $this->publicUrl($assets['signaturePath']),
            'stamp_url' => $this->publicUrl($assets['stampPath']),
            'using_default_signature' => !$setting?->signature_path,
            'using_default_stamp' => !$setting?->stamp_path,
            'updated_at' => $setting?->updated_at,
            'updated_by' => $setting?->updatedBy,
        ];
    }

    public function update(?UploadedFile $signature, ?UploadedFile $stamp, User $user): array
    {
        $paths = [];

        if ($signature) {
            $paths['signature_path'] = $this->store($signature, 'signature');
        }

        if ($stamp) {
            $paths['stamp_path'] = $this->store($stamp, 'stamp');
        }

        DB::transaction(function () use ($paths, $user): void {
            $setting = $this->currentSetting() ?: new LetterBrandingSetting();
            $setting->fill($paths);
            $setting->updated_by = $user->getKey();
            $setting->save();
        });

        return $this->summary();
    }

    public function reset(User $user): array
    {
        $setting = $this->currentSetting() ?: new LetterBrandingSetting();
        $setting->forceFill([
            'signature_path' => null,
            'stamp_path' => null,
            'updated_by' => $user->getKey(),
        ])->save();

        return $this->summary();
    }

    private function currentSetting(): ?LetterBrandingSetting
    {
        try {
            return LetterBrandingSetting::with('updatedBy:id,first_name,middle_name,last_name,email')->first();
        } catch (\Throwable) {
            return null;
        }
    }

    private function store(UploadedFile $file, string $type): string
    {
        $directory = public_path(self::UPLOAD_DIRECTORY);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $extension = strtolower($file->extension() ?: 'png');
        $name = $type . '-' . Str::uuid() . '.' . $extension;
        $file->move($directory, $name);

        return self::UPLOAD_DIRECTORY . '/' . $name;
    }

    private function publicUrl(string $path): string
    {
        return asset(ltrim($path, '/'));
    }

    private function dataUri(string $path): string
    {
        $absolutePath = public_path(ltrim($path, '/'));

        if (!is_file($absolutePath)) {
            return '';
        }

        $mime = mime_content_type($absolutePath) ?: 'image/png';

        return 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($absolutePath));
    }
}
