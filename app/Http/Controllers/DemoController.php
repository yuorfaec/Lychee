<?php

namespace App\Http\Controllers;

use App\Actions\Album\Prepare;
use App\Actions\Albums\Prepare as AlbumsPrepare;
use App\Actions\Albums\Smart;
use App\Actions\Albums\Top;
use App\Models\Album;
use App\Models\Configs;
use App\Models\Photo;
use Response;

class DemoController extends Controller
{
	/**
	 * This function returns what are the possible return output to simulate
	 * the server interaction in the case of the demo server here:
	 * https://lycheeorg.github.io/demo/.
	 *
	 * Call /demo and use the generated code to replace the api.post() function
	 *
	 * @return \Illuminate\Http\Response|string
	 */
	public function js()
	{
		if (Configs::get_value('gen_demo_js', '0') != '1') {
			return redirect()->route('home');
		}

		$functions = [];

		/**
		 * Session::init.
		 */
		$session_init = resolve(SessionController::class);
		$return_session = [];
		$return_session['name'] = 'Session::init()';
		$return_session['type'] = 'string';
		$return_session['data'] = json_encode($session_init->init());

		$functions[] = $return_session;

		/**
		 * Albums::get.
		 */
		$albums_controller = resolve(AlbumsController::class);
		$top = resolve(Top::class);
		$smart = resolve(Smart::class);
		$prepareAlbums = resolve(AlbumsPrepare::class);

		$return_albums = [];
		$return_albums['name'] = 'Albums::get';
		$return_albums['type'] = 'string';
		$return_albums['data'] = json_encode($albums_controller->get($top, $smart, $prepareAlbums));

		$functions[] = $return_albums;

		/**
		 * Album::get.
		 */
		$return_album_list = [];
		$return_album_list['name'] = 'Album::get';
		$return_album_list['type'] = 'array';
		$return_album_list['kind'] = 'albumID';
		$return_album_list['array'] = [];

		/**
		 * @var Collection<Album>
		 */
		$albums = Album::where('public', '=', '1')
			->where('viewable', '=', '1')
			->get();
		/*
		 * @var Album
		 */
		$prepare = resolve(Prepare::class);
		foreach ($albums as $album) {
			/**
			 * Copy paste from Album::get().
			 */
			// Get photos
			// Get album information

			$return_album_json = $prepare->do($album);

			$return_album = [];
			$return_album['id'] = $album->id;
			$return_album['data'] = json_encode($return_album_json);

			$return_album_list['array'][] = $return_album;
		}

		$functions[] = $return_album_list;

		/**
		 * Photo::get.
		 */
		$return_photo_list = [];
		$return_photo_list['name'] = 'Photo::get';
		$return_photo_list['type'] = 'array';
		$return_photo_list['kind'] = 'photoID';
		$return_photo_list['array'] = [];

		foreach ($albums as $album) {
			/** @var Photo $photo */
			foreach ($album->photos as $photo) {
				$return_photo = [];
				$return_photo_json = $photo->toReturnArray();
				$return_photo_json['original_album'] = $return_photo_json['album'];
				$return_photo_json['album'] = $album->id;
				$return_photo['id'] = $photo->id;
				$return_photo['data'] = json_encode($return_photo_json);

				$return_photo_list['array'][] = $return_photo;
			}
		}

		$functions[] = $return_photo_list;

		$contents = view('demo', ['functions' => $functions]);
		$response = Response::make($contents, 200);
		$response->header('Content-Type', 'text/plain');

		return $response;
	}
}
