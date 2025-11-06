<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravolt\Avatar\Facade as Avatar;

class FileService
{
    private const DEFAULT_FOLDER = 'files';

    private const IMAGE_FOLDER = 'images';

    private const DISK = 'public';

    public static function fakeImage(string $name = 'image', string $shape = 'square', string $folder = self::IMAGE_FOLDER): string
    {
        Storage::disk(self::DISK)->makeDirectory($folder);
        $photo = "$folder/".Str::random(30).'.webp';
        Avatar::create($name)->setShape($shape)->save(storage_path("app/public/$photo"));

        return $photo;
    }

    public static function delete(?string $file): void
    {
        if ($file) {
            Storage::disk(self::DISK)->delete($file);
        }
    }

    public static function getType(UploadedFile $file): string
    {
        return match ($file->getClientOriginalExtension()) {
            'jpeg', 'jpg', 'png' => 'image',
            'pdf' => 'pdf',
            'mp4', 'webm', 'ogg' => 'video',
            default => 'file',
        };
    }

    public static function save(?UploadedFile $file, string $folder = self::DEFAULT_FOLDER): ?string
    {
        // check if the folder exists
        if (! Storage::disk(self::DISK)->exists($folder)) {
            Storage::disk(self::DISK)->makeDirectory($folder);
        }

        return $file ? Storage::disk(self::DISK)->put($folder, $file) : null;
    }

    public static function update(?string $oldPath, ?UploadedFile $file, string $folder = self::DEFAULT_FOLDER): string
    {
        if ($file) {
            self::delete($oldPath);

            return Storage::disk(self::DISK)->put($folder, $file);
        }

        return $oldPath ?? '';
    }

    public static function get(?string $path): ?string
    {
        if (!$path) {
            return null;
        }
        $url = Storage::disk(self::DISK)->url($path);
        // إجبار استخدام HTTPS
        if (config('app.env') === 'production' || request()->secure()) {
            $url = str_replace('http://', 'https://', $url);
        }
        return $url;
    }

    public static function saveFromTemp(string $tempPath, string $folder = self::DEFAULT_FOLDER): string
    {
        $newPath = "$folder/".basename($tempPath);
        Storage::disk(self::DISK)->move($tempPath, $newPath);

        return $newPath;
    }

    public static function updateFromTemp(?string $oldPath, ?string $tempPath, string $folder = self::DEFAULT_FOLDER): string
    {
        if ($tempPath) {
            $newPath = "$folder/".basename($tempPath);
            Storage::disk(self::DISK)->move($tempPath, $newPath);
            self::delete($oldPath);

            return $newPath;
        }

        return $oldPath ?? '';
    }
}
