<?php

namespace App;

use App\LawDocumentElement;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class LawDocument extends Model
{
    public static function listAllWithElementsAndContent($ids=[],$byIds=false) {

        $cache_key = 'IUS-LD-LAWEAC-' . join('-',$ids) . ($byIds ? '-byid' : '');

        if (Cache::has($cache_key)) {
            return Cache::get($cache_key);
        }

        set_time_limit(180);

        if (empty($ids)) {
            $tmp = LawDocument::select('id', 'updated_at', 'name', 'identifier', 'signed_at', 'info')->get();
        } else {
            $tmp = LawDocument::select('id', 'updated_at', 'name', 'identifier', 'signed_at', 'info')->whereIn('id',$ids)->get();
        }
        $results=[];
        foreach($tmp as $d) {
            $row = [];
            $row['id'] = $d->id;
            $row['label'] = $d->name . ' ' . $d->identifier;
            // . ' ' . $d->signed_at;
            $row['elements'] = self::documentsListAllElements($d->id,$byIds);
            if ($byIds) {
                $results[$d->id]=$row;
            } else {
                $results[]=$row;
            }
        }

        $all_keys = Cache::get('IUS-LD-LAWEAC',[]);
        $all_keys[$cache_key] = $cache_key;
        Cache::put('IUS-LD-LAWEAC',$all_keys);
        Cache::put($cache_key,$results);

        return $results;
    }

    public static function listAllWithElementsAndContentChapter($ids=[],$byIds=false) {

        $cache_key = 'IUS-LD-LAWEAC1-' . join('-',$ids) . ($byIds ? '-byid' : '');

        if (Cache::has($cache_key)) {
            return Cache::get($cache_key);
        }

        set_time_limit(180);

        if (empty($ids)) {
            $tmp = LawDocument::select('id', 'updated_at', 'name', 'identifier', 'signed_at', 'info')->get();
        } else {
            $tmp = LawDocument::select('id', 'updated_at', 'name', 'identifier', 'signed_at', 'info')->whereIn('id',$ids)->get();
        }
        $results=[];
        foreach($tmp as $d) {
            $row = [];
            $row['id'] = $d->id;
            $row['label'] = $d->name . ' ' . $d->identifier;
            // . ' ' . $d->signed_at;
            $row['elements'] = self::documentsListAllChapters($d->id,$byIds);
            if ($byIds) {
                $results[$d->id]=$row;
            } else {
                $results[]=$row;
            }
        }

        $all_keys = Cache::get('IUS-LD-LAWEAC1',[]);
        $all_keys[$cache_key] = $cache_key;
        Cache::put('IUS-LD-LAWEAC1',$all_keys);
        Cache::put($cache_key,$results);

        return $results;
    }

    public static function clearCache() {
        self::clearCache0();
        self::clearCache1();
    }

    public static function clearCache0() {
        if (!Cache::has('IUS-LD-LAWEAC')) {
            return;
        }
        $keys = Cache::get('IUS-LD-LAWEAC');
        foreach($keys as $k) {
            Cache::forget($k);
        }
        Cache::forget('IUS-LD-LAWEAC');
    }

    public static function clearCache1() {
        if (!Cache::has('IUS-LD-LAWEAC1')) {
            return;
        }
        $keys = Cache::get('IUS-LD-LAWEAC1');
        foreach($keys as $k) {
            Cache::forget($k);
        }
        Cache::forget('IUS-LD-LAWEAC1');
    }

    protected static function documentsListAllElements($lid,$byIds=false) {
        $ldes = LawDocumentElement::where('law_document_id', $lid)->orderBy('depth', 'asc')->orderBy('position', 'asc')->get();
        $labels=[];
        $parents=[];
        $keys=[];
        $all=[];
        foreach($ldes as $e) {
            $a = array($e->name,$e->enumeration,$e->title);
            $labels[$e->id]= trim(str_replace('  ',' ', join(' ', $a)));
            $parents[$e->id]=$e->parent_element_id;
            $keys[$e->id] = $e->position;
            if (!empty($e->parent_element_id)) {
                $keys[$e->id] = $keys[$e->parent_element_id] . '.' . $keys[$e->id];
            }
            $all[$e->id] = $e->content;
        }
        $keys = array_flip($keys);
        ksort($keys);
        $results=[];
        foreach($keys as $id) {
            if (empty($all[$id])) {
                continue;
            }
            if($byIds) {
                $results[$id] = [
                  'id' => $id,
                  'label' => trim(str_replace('  ',' ', self::parentLabel($labels,$parents,$id) . ' ' . $labels[$id])),
                  'content' => $all[$id],
                ];
            } else {
                $results[] = [
                  'id' => $id,
                  'label' => trim(str_replace('  ',' ', self::parentLabel($labels,$parents,$id) . ' ' . $labels[$id])),
                  'content' => $all[$id],
                ];
            }
        }
        return $results;
    }

    protected static function documentsListAllChapters($lid,$byIds=false) {
        $ldes = LawDocumentElement::where('law_document_id', $lid)->where('depth', '=' , '0')->orderBy('position', 'asc')->get();
        $labels=[];
        $keys=[];
        $all=[];
        $results=[];
        foreach($ldes as $e) {
            $a = array($e->name,$e->enumeration,$e->title);
            $labels[$e->id]= trim(str_replace('  ',' ', join(' ', $a)));
            $all[$e->id] = $e->content;
            if($byIds) {
                $results[$e->id] = [
                    'id' => $e->id,
                    'label' => $labels[$e->id],
                    'content' => $all[$e->id],
                ];
            } else {
                $results[] = [
                    'id' => $e->id,
                    'label' => $labels[$e->id],
                    'content' => $all[$e->id],
                ];
            }
        }

        return $results;
    }

    protected static function parentLabel($labels,$parents,$id) {
        if (empty($parents[$id])) {
          return '';
        }
        return trim(str_replace('  ',' ', self::parentLabel($labels,$parents,$parents[$id]) . ' ' . $labels[$parents[$id]]));
    }
}
