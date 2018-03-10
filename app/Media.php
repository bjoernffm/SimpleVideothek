<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Media extends Model
{
    //
    public function getChildren()
    {
        $children = DB::select('SELECT o.id, COUNT(p.id)-1 AS level FROM media AS n, media AS p,
        media AS o WHERE o.left BETWEEN p.left AND p.right AND o.left BETWEEN n.left AND n.right AND
        n.id = ? GROUP BY o.id ORDER BY o.left', [$this->id]);

        $childrenIds = [];
        $level = $children[0]->level+1;
        foreach($children as $child) {
            if ($child->level == $level) {
                $childrenIds[] = $child->id;
            }
        }

        $medias = \App\Media::find($childrenIds);
        return $medias;
    }

    public function getPath()
    {
        $parents = DB::select('SELECT p.*
                                FROM
                                    media n, media p
                                WHERE
                                    n.left BETWEEN p.left AND p.right AND
                                    n.id = ?
                                ORDER BY n.left', [$this->id]);
        
        $parentIds = [];
        foreach($parents as $parent) {
                $parentIds[] = $parent->id;
        }

        $medias = \App\Media::find($parentIds);
        return $medias;
    }
}
