<?php

namespace App\Http\Controllers;

use App\Models\Album;
use App\Models\Card;
use App\Models\picture;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Validator;

class GalleryController extends Controller
{
    function index(Request $request) {
        $albumImages = $this->getFormattedAlbumImages();

        return inertia('Gallery', ['albumImages' => $albumImages]);
    }

    function getFormattedAlbumImages() {
        $albums = Album::all();
        $images = Picture::all();

        $albumImages = [];
        foreach ($albums as $album) {
            $newRow = [];
            $newRow['id'] = $album->id;
            $newRow['name'] = $album->name;
            $newRow['year'] = $album->year;
            $newRow['images'] = [];
            foreach ($images as $image) {
                if ($album->id == $image->album_id) {
                    array_push($newRow['images'], $image);
                }
            }
            array_push($albumImages, $newRow);
        }
        $albumImages = array_reverse($albumImages);
        return $albumImages;
    }
    function store(Request $request) {

        $validation = Validator::make($request->all(), [
            'year' => 'required|integer',
            'name' => 'required|unique:albums,name|min:1',
        ],[
            'year.required' => 'Un année est requise (value)',
            'year.integer' => 'Un année est requise (int)',
            'name.required'=> 'Un nom est obligatoire',
            'name.unique' => 'Le nom de l\'album existe déjà',
            'name.min' => 'Un nom est obligatoire',
        ]);
        if ($validation->fails()) {
            return response()->json(['errors' => $validation->errors()], 422);
        }

        $newAlbum = new Album();
        $newAlbum->name = $request->name;
        $newAlbum->year = $request->year;

        try {
            $newAlbum->save();
            $newId = $newAlbum->id;
            $albumImages = $this->getFormattedAlbumImages();
            return response()->json([[__('Succès'), __('L\'album a été créé.')], 'album_id' => $newId, 'albumImages' => $albumImages], 200);
        }
        catch (Exception $e) {
            report($e);
            return response()->json($e->getMessage(), 400);
        }
    }

    function destroy(Request $request, $id) {
        $album = Album::find($id);

        if ($album) {
            try {
                $album->delete();

                $folder = "uploads/albums/" . $album->name;
                Storage::disk('public')->deleteDirectory($folder);

                return response()->json([[__('Succès'), __('L\'album a été supprimé.')], 'album_id' => $id], 200);
            }
            catch (Exception $e) {
                report($e);
                return response()->json($e->getMessage(), 400);
            }
        }
        return response()->json([__('Erreur')], 422);
    }

    function addPhoto (Request $request, $id) {
        $album = Album::find($id);

        if ($album) {
            $validation = Validator::make($request->all(), [
                'image' => ['required', 'regex:/^data:image\/(jpeg|png|jpg);base64,/'],
            ],[
                'image.required' => 'Un image est obligatoire',
                'image.regex' => __('Le fichier n\'est pas de type .jpg, .jpeg ou png.'),
            ]);
            $validation->after(function ($validation) use ($album, &$request) {
                if ($request->has('image')) {

                    $data = $request->input('image');

                    // strip off the "data:image/png;base64," part
                    [$meta, $content] = explode(',', $data);
                    $decoded = base64_decode($content);
                    if (!$decoded) {
                        $validation->errors()->add('image', __('Le fichier n\'est pas valide.'));
                    }

                    // check image size
                    $maxSizeMB = 2; // change this to change image size in mb.
                    $maxSizeBytes = $maxSizeMB * 1024 * 1024;
                    if (strlen($decoded) > $maxSizeBytes) {
                        $validation->errors()->add('image', __('L\'image ne doit pas être plus grande que ' . $maxSizeMB . 'mb.'));
                    }

                    // verify if image is actually an image of type selected
                    $finfo = finfo_open();
                    $mimeType = finfo_buffer($finfo, $decoded, FILEINFO_MIME_TYPE);
                    finfo_close($finfo);
                    if (!in_array($mimeType, array("image/png", "image/jpeg", "image/jpg"))) {
                        $validation->errors()->add('image', __('Le fichier n\'est pas de type .jpg, .jpeg ou png.'));
                    }

                    $extension = explode('/', explode(';', $meta)[0])[1]; // e.g. "png"
                    $filename = uniqid() . "." . $extension;

                    Storage::disk('public')->put("uploads/albums/" . $album->name . "/" . $filename, $decoded);
                    $imagePath = "uploads/albums/" . $album->name . "/" . $filename;

                    $request->merge(['image_path' => $imagePath]);
                }
            });
            if ($validation->fails()) {
                return response()->json(['errors' => $validation->errors()], 422);
            }

            try {
                $newPicture = new Picture();
                $newPicture->picture_path = $request->image_path;
                $newPicture->album_id = $album->id;
                $newPicture->created_at = now();
                $newPicture->save();

                $images = Picture::where('album_id', $id)->get();

                return response()->json([[__('Succès'), __('La photo a été ajoutée.')], "images" => $images], 200);
            }
            catch (Exception $e) {
                report($e);
                return response()->json([__('Erreur'), __('Une erreur est survenu lors de l\'ajout de la photo dans l\'album.')], 400);
            }
        }
        else {
            return response()->json([__('Erreur'), __('L\'album est invalide')], 422);
        }
    }

    function destroyPicture(Request $request, $id) {
        $picture = Picture::find($id);
        $album = Album::find($picture->album_id);

        if (!$picture) {
            return response()->json([__('Erreur'), __('La photo est invalide')], 422);
        }

        try {
            $picture->delete();
            $picture_id = $picture->id;
            Storage::disk('public')->delete($picture->picture_path);

            $folder = "uploads/albums/" . $album->name;
            $files = Storage::disk('public')->files($folder);
            if (empty($files)) {
                Storage::disk('public')->deleteDirectory("uploads/albums/" . $album->name);
            }

            $images = Picture::where('album_id', $picture->album_id)->get();

            return response()->json([[__('Succès'), __('La photo a été supprimé.')], "images" => $images], 200);
        }
        catch (Exception $e) {
            report($e);
            return response()->json($e->getMessage(), 400);
        }
    }
}
