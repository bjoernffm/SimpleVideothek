<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    public function getChildren($all = false)
    {
        $children = DB::select('SELECT o.id, COUNT(p.id)-1 AS level FROM media AS n, media AS p,
        media AS o WHERE o.left BETWEEN p.left AND p.right AND o.left BETWEEN n.left AND n.right AND
        n.id = ? GROUP BY o.id ORDER BY o.left', [$this->id]);

        $childrenIds = [];
        $level = $children[0]->level+1;
        foreach($children as $child) {
            if ($child->level == $level or $all) {
                $childrenIds[] = $child->id;
            }
        }

        $medias = Media::find($childrenIds);
        return $medias;
    }

    public function getDirectories()
    {
        $directories = DB::select('SELECT o.id, COUNT(p.id)-1 AS level FROM media AS n, media AS p,
        media AS o WHERE o.left BETWEEN p.left AND p.right AND o.left BETWEEN n.left AND n.right AND
        o.type IN ("DIRECTORY", "COMIC") AND n.id = ? GROUP BY o.id ORDER BY o.left', [$this->id]);

        $buffer = [];
        foreach($directories as $directory) {
            $media = Media::find($directory->id);
            $buffer[] = ['media' => $media, 'level' => $directory->level];
        }

        return $buffer;
    }

    public function getDirectoriesForSelect()
    {
        $directories = $this->getDirectories();
        
        $buffer = [];
        foreach($directories as $directory) {
            $buffer[$directory['media']->uuid] = str_repeat('&nbsp;', $directory['level']*4).$directory['media']->title;
        }

        return $buffer;
    }

    public function getPath()
    {
        $parents = DB::select('SELECT p.*
                                FROM
                                    media n, media p
                                WHERE
                                    n.left BETWEEN p.left AND p.right AND
                                    n.id = ?
                                ORDER BY p.left', [$this->id]);

        $medias = [];
        foreach($parents as $parent) {
                $parentIds[] = $parent->id;
        }

        $medias = Media::find($parentIds)->sortBy('left');
        return $medias;
    }

    public function appendChild(Media $media)
    {
        DB::transaction(function() use ($media) {
            DB::table('media')
                ->where('right', '>=', $this->right)
                ->increment('right', 2);

            DB::table('media')
                ->where('left', '>', $this->right)
                ->increment('left', 2);

            $media->left = $this->right;
            $media->right = $this->right+1;
            $media->save();

            $this->right += 2;
        });
    }

    public function delete()
    {
        DB::transaction(function() {
            parent::delete();

            // rise children to current level
            DB::table('media')
                ->whereBetween('left', [$this->left, $this->right])
                ->decrement('left', 1);
            DB::table('media')
                ->whereBetween('left', [$this->left, $this->right])
                ->decrement('right', 1);

            // repair table
            DB::table('media')
                ->where('left', '>', $this->right)
                ->decrement('left', 2);
            DB::table('media')
                ->where('right', '>', $this->right)
                ->decrement('right', 2);

            // deleting possible files
            try {
                if ($this->file != '') {
                    Storage::delete('media/files/' . $this->file);
                    Storage::delete([
                        'media/files/' . $this->file,
                        'pending/' .  $this->file
                    ]);
                }

                if ($this->thumbnail != '') {
                    Storage::delete([
                        'media/seek_thumbnails/' .  $this->thumbnail,
                        'media/thumbnails/' .  $this->thumbnail
                    ]);
                }
            } catch(Exception $e) {}
        });
    }

    public function getFormattedLengthAttribute()
    {
        if ($this->length == null) {
            return '';
        }

        return $this->formatTime($this->length);
    }

    protected function formatTime($secondsParam)
    {
        $seconds = $secondsParam % 60;
        $minutes = ($secondsParam/60) % 60;
        $hours = ($secondsParam/3600) % 24;
        $days = ($secondsParam/86400) % 365;

        if ($days > 0) {
            return sprintf('%03d:%02d:%02d:%02d', $days, $hours, $minutes, $seconds);
        }

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
