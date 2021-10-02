<?php

namespace App\Http\Requests\Photo;

use App\Http\Requests\BaseApiRequest;
use App\Http\Requests\Contracts\HasAlbumID;
use App\Http\Requests\Traits\HasAlbumIDTrait;
use App\Rules\AlbumIDRule;
use Illuminate\Http\UploadedFile;

class AddPhotoRequest extends BaseApiRequest implements HasAlbumID
{
	use HasAlbumIDTrait;

	protected UploadedFile $file;

	/**
	 * {@inheritDoc}
	 */
	public function authorize(): bool
	{
		return $this->authorizeAlbumWrite([$this->albumID]);
	}

	/**
	 * {@inheritDoc}
	 */
	public function rules(): array
	{
		return [
			HasAlbumID::ALBUM_ID_ATTRIBUTE => ['required', new AlbumIDRule()],
			'0' => 'required|file',
		];
	}

	/**
	 * {@inheritDoc}
	 */
	protected function processValidatedValues(array $values, array $files): void
	{
		$this->albumID = $values[HasAlbumID::ALBUM_ID_ATTRIBUTE];
		if (empty($this->albumID)) {
			$this->albumID = null;
		}
		$this->file = $files[0];
	}

	public function file(): UploadedFile
	{
		return $this->file;
	}
}