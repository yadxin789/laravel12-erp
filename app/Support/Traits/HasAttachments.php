<?php

namespace App\Support\Traits;

use App\Models\System\Attachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HasAttachments
{
    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderBy('created_at', 'desc');
    }

    public function addAttachment(UploadedFile $file, ?string $description = null): Attachment
    {
        $path = $file->store('attachments/' . class_basename($this->getMorphClass()), 'public');

        return $this->attachments()->create([
            'filename' => $file->getClientOriginalName(),
            'path' => $path,
            'mime_type' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'description' => $description,
            'uploaded_by' => auth()->id(),
        ]);
    }
}
