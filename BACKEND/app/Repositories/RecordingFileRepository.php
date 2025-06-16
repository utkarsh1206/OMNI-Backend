<?php
namespace App\Repositories;

use App\Http\Response\CustomApiResponse;
use Illuminate\Support\Facades\File;

class RecordingFileRepository
{
    protected $recordingPath;
    private $apiResponse;

    public function __construct(CustomApiResponse $customApiResponse)
    {
        $this->recordingPath = base_path(config('omni.RECORDING_FILE_PATH'));
        $this->apiResponse = $customApiResponse;
    }

    public function findRecordingFile($request)
    {
        $uniqueId = $request->input('unique_id');

        if (!$uniqueId) {
            return response()->json(['error' => 'unique_id is required']);
        }

        if (!is_dir($this->recordingPath)) {
            return response()->json(['error' => 'Recording directory not found']);
        }

        $files = File::files($this->recordingPath);

        foreach ($files as $file) {
            $filename = $file->getFilename();

            if (str_contains($filename, $uniqueId)) {
                $data = [
                    'filename' => $filename,
                    'url' => asset("storage/recordings/$filename")
                ];

                return $this->apiResponse->getResponseStructure(
                    trans('message.success'),
                    $data,
                    trans('message.data_fetch')
                );
            }
        }

        return response()->json(['error' => 'File not found']);
    }
}
