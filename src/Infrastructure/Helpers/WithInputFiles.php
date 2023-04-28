<?php

namespace Ebolution\Core\Infrastructure\Helpers;

use Illuminate\Http\Request;

trait WithInputFiles
{
    /**
     * @var string Storage folder (within storage/app/).
     *             Use 'public' if files should be accessible
     *             on the web. Use any other value for private files.
     */
    private string $storage_folder = 'public';

    /** @var string Storage sub folder (within $storage_folder).
     *              Generate an independent storage location by naming
     *              this sub folder with something unique to your module
     *              like: <your-module-name>/uploads/ (end with /)
     */
    private string $storage_sub_folder = '';

    /**
     * Store the files included on the request on local storage and return local storage paths.
     *
     * @param Request $request Current request
     * @param array $file_keys List of file fields that might be included on the request
     * @return array For each file, the local storage path or null if file is not present
     */
    public function storeFiles(Request $request, array $file_keys): array
    {
        $file_paths = [];
        foreach($file_keys as $key) {
            $file_paths[$key] = $this->storeSingleFile($request, $key);
        }
        return $file_paths;
    }

    private function storeSingleFile(Request $request, string $key): ?string
    {
        if ($request->hasFile($key) and $request->file($key)->isValid()) {
            $file = $request->$key;

            $extension = $file->extension();
            $name = uniqid();

            $path =  $file->storeAs($this->storage_folder, "{$this->storage_sub_folder}{$name}.{$extension}");

            // Assuming that command 'php artisan storage:link' has been executed
            // to allow these files to be publicly accessible.
            // storage/app/public --> public/storage
            // This returns the public path (uri) to the file (<APP_URL>/<path>)
            // If not using public storage location, path is not transformed.
            return $this->storage_folder !==  'public' ? $path :
                'storage' . substr($path, strlen($this->storage_folder));
        }
        return null;
    }
}
