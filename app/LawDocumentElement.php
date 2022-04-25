<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\Models\Media;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class LawDocumentElement extends Model  implements HasMedia
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'law_documents_elements';
    use HasMediaTrait;

    public static function getChildrenIds($targetId, $childrenIds, $set_date=null){
        if($set_date){
            $children = LawDocumentElement::where('parent_element_id', $targetId)
                ->where(function ($query) use ($set_date){
                    $query->whereDate('start_at', '<=', $set_date)->orWhereNull('start_at');
                })
                ->where(function ($query1) use ($set_date){
                    $query1->whereDate('end_at', '>=', $set_date)->orWhereNull('end_at');
                })
                ->orderBy('depth', 'asc')->orderBy('position', 'asc')->get();
        }
        else{
            $children = LawDocumentElement::where('parent_element_id', $targetId)->orderBy('depth', 'asc')->orderBy('position', 'asc')->get();
        }
        foreach($children as $child){
            $childrenIds[] = $child->id;
            $childrenIds = LawDocumentElement::getChildrenIds($child->id, $childrenIds);
        }
        return $childrenIds;
    }

}
