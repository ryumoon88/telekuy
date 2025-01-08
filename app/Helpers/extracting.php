<?php

// use ZipArchive;
// use RarArchive;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;


function extractAccounts($filePath)
{
    // Save the uploaded file temporarily
    $filePath = Storage::disk('local')->path($filePath);
    // Determine if the file is a ZIP or RAR file
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

    $baseExtractionPath = 'accounts';

    if ($extension === 'zip') {
        // Process ZIP file
        return extractFolderWithTData($filePath, $baseExtractionPath);
    } elseif ($extension === 'rar') {
        // Process RAR file
        // return extractFromRar($filePath, $baseExtractionPath);
    } else {
        throw new \Exception('Unsupported file type. Only ZIP and RAR are allowed.');
    }
}
function extractFolderWithTData($zipFilePath, $destinationPath)
{
    $zip = new ZipArchive();

    if (!$zip->open($zipFilePath)) {
        throw new \Exception("Failed to open the ZIP archive.");
    }

    $destinations = collect();

    for ($i = 0; $i < $zip->numFiles; $i++) {
        $fileName = $zip->getNameIndex($i);

        // Skip directories
        if (str_ends_with($fileName, '/')) continue;

        // Check if the file is inside a "tdata" folder
        $parts = explode('/', $fileName);
        if (($tdataIndex = array_search('tdata', $parts)) !== false) {
            $parentFolder = $parts[$tdataIndex - 1] ?? null;
            $relativePath = implode('/', array_slice($parts, $tdataIndex + 1));
            $destinations->push([
                'parentFolder' => $parentFolder,
                'filePath' => $fileName,
                'destination' => "{$destinationPath}/{$parentFolder}/tdata/{$relativePath}",
            ]);
        }
    }

    $extractedFolders = $destinations->groupBy('parentFolder')->keys();

    $destinations->each(function ($file) use ($zip) {
        Storage::disk('local')->put(
            $file['destination'],
            stream_get_contents($zip->getStream($file['filePath']))
        );
    });

    $zip->close();

    return $extractedFolders->map(fn($folder) => "{$folder}:accounts/{$folder}")->implode(',');
}

function extractTopLevelFoldersFromZip($archivePath, $destinationPath)
{
    $zip = new ZipArchive();
    $folders = [];
    if ($zip->open($archivePath) === true) {
        // Iterate through all files in the zip archive
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $fileName = $zip->getNameIndex($i);

            // If it's a directory and it's a top-level folder (not nested)
            if (substr($fileName, -1) === '/') {
                // Get the name of the folder (parent folder)
                $folderParts = explode('/', $fileName);
                $name = $folderParts[0]; // First part of the folder name (top-level)
                $folderName = uniqid();
                $folders[] = $name;
                // Create a destination folder path using the top-level folder name
                $folderDestination = $destinationPath;
                // Extract only the files in the top-level folder (without nested folders)
                foreach (range(0, $zip->numFiles - 1) as $j) {
                    $currentFile = $zip->getNameIndex($j);

                    // Only extract files that belong to the top-level folder
                    if (strpos($currentFile, $name . '/') === 0 && substr($currentFile, -1) !== '/') {
                        // Extract the file to the parent folder
                        $zip->extractTo($folderDestination, $currentFile);
                    }
                }
            }
        }

        $zip->close();
        return Arr::join(array_unique($folders), ','); // Successfully extracted
    }

    return false; // If the ZIP file could not be opened
}

function zipFolders(array $folderPaths, string $outputZip)
{
    $zip = new ZipArchive();

    if ($zip->open($outputZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) === true) {
        foreach ($folderPaths as $folderPath) {
            $folderPath = realpath($folderPath); // Ensure absolute path

            if ($folderPath === false || !is_dir($folderPath)) {
                echo "Skipping invalid folder: $folderPath\n";
                continue;
            }

            $folderParts = explode("\\", $folderPath);
            $parentName = $folderParts[count($folderParts) - 1];

            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($folderPath),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    $relativePath = substr($filePath, strlen($folderPath) - strlen($parentName)); // Path relative to the folder
                    $zip->addFile($filePath, $relativePath);
                }
            }
        }

        $zip->close();
        return true;
    }
    return false;
}

function Zip($source, $destination)
{
    $source_arr = [];
    if (is_string($source))
        $source_arr = array($source); // convert it to array
    $source_arr = $source;

    if (!extension_loaded('zip')) {
        return false;
    }

    $zip = new ZipArchive();
    if ($zip->open($destination, ZipArchive::CREATE)) {
        foreach ($source_arr as $source) {
            // if (!file_exists($source)) continue;
            // $source = str_replace('\\', '/', realpath($source));
            $source = Storage::disk('public')->path($source);
            if (is_dir($source) === true) {
                $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
                foreach ($files as $file) {
                    $file = str_replace('\\', '/', realpath($file));

                    if (is_dir($file) === true) {
                        $zip->addEmptyDir(str_replace($source . '/', '', $file . '/'));
                    } else if (is_file($file) === true) {
                        $zip->addFromString(str_replace($source . '/', '', $file), file_get_contents($file));
                    }
                }
            } else if (is_file($source) === true) {
                $zip->addFromString(basename($source), file_get_contents($source));
            }
        }
        $zip->close();
    }
    return true;
}
