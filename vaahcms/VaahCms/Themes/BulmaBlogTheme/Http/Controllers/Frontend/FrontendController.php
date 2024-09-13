<?php namespace VaahCms\Themes\BulmaBlogTheme\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Cms\Entities\Content;

class FrontendController extends Controller
{


    public function __construct()
    {

    }

    public function index()
    {
        return view('bulmablogtheme::frontend.home');
    }

    public function searchResult(Request $request)
    {

        $query = Content::leftJoin('vh_cms_content_types', function ($join) {
            $join->on('vh_cms_content_types.id', 'vh_cms_contents.vh_cms_content_type_id');
        })->join('vh_cms_content_form_fields', function ($join) {
            $join->on('vh_cms_content_form_fields.vh_cms_content_id', '=', 'vh_cms_contents.id');
        })->join('vh_cms_form_fields', function ($join) {
            $join->on('vh_cms_form_fields.id', '=', 'vh_cms_content_form_fields.vh_cms_form_field_id');
        })->where('vh_cms_content_types.slug', 'blog')->where('vh_cms_contents.status', 'published');

        if(isset($request->q) && $request->q){
            $query->where('vh_cms_form_fields.slug', 'title')
                ->where('vh_cms_content_form_fields.content', 'like', "%" . $request->q . "%");
        }

        $data = $query->orderBy('vh_cms_contents.created_at', 'desc')
            ->select('vh_cms_contents.id', 'vh_cms_contents.created_at')
            ->distinct()->simplePaginate(6);


        return view('bulmablogtheme::frontend.pages.search-result')
            ->with(['data'=> $data->appends(request()->query())]);
    }

    public function searchCategory(Request $request,$slug)
    {

        $query = Content::leftJoin('vh_cms_content_types', function ($join) {
            $join->on('vh_cms_content_types.id', 'vh_cms_contents.vh_cms_content_type_id');
        })->join('vh_cms_content_form_fields', function ($join) {
            $join->on('vh_cms_content_form_fields.vh_cms_content_id', '=', 'vh_cms_contents.id');
        })->join('vh_cms_form_fields', function ($join) {
            $join->on('vh_cms_form_fields.id', '=', 'vh_cms_content_form_fields.vh_cms_form_field_id');
        })->where('vh_cms_content_types.slug', 'blog')->where('vh_cms_contents.status', 'published');

        if ($slug) {
            $query->where('vh_cms_form_fields.slug', 'category')
                ->where('vh_cms_content_form_fields.content', $slug);
        }

        $data = $query->orderBy('vh_cms_contents.created_at', 'desc')
            ->select('vh_cms_contents.id', 'vh_cms_contents.created_at')
            ->distinct()->simplePaginate(6);


        return view('bulmablogtheme::frontend.pages.category-result')
            ->with(['data'=> $data,'category_slug'=>$slug]);
    }

}
