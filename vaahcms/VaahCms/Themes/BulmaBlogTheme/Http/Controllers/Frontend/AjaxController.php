<?php namespace VaahCms\Themes\BulmaBlogTheme\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use VaahCms\Modules\Cms\Entities\Content;
use VaahCms\Themes\BulmaBlogTheme\Mail\ContactFormMail;

class AjaxController extends Controller
{


    public function __construct()
    {

    }

    public function getBlogList(Request $request)
    {

        $query_result = $this->getQueryResult($request);

        $category_result = $this->getCategoryResult($request);

        if(isset($request->q) && $request->q && $request->q != 'null'
            && isset($request->category) && $request->category && $request->category != 'all'){
            $list = array_intersect($query_result->all(),$category_result->all());
        }elseif(isset($request->q) && $request->q && $request->q != 'null'){
            $list = $query_result;
        }else{
            $list = $category_result;
        }

        return view('bulmablogtheme::frontend.partials.blog-list')->with('blogs', $list);
    }

    public function getQueryResult(Request $request)
    {

        $query = Content::leftJoin('vh_cms_content_types', function ($join) {
            $join->on('vh_cms_content_types.id', 'vh_cms_contents.vh_cms_content_type_id');
        })->join('vh_cms_content_form_fields', function ($join) {
            $join->on('vh_cms_content_form_fields.vh_cms_content_id', '=', 'vh_cms_contents.id');
        })->join('vh_cms_form_fields', function ($join) {
            $join->on('vh_cms_form_fields.id', '=', 'vh_cms_content_form_fields.vh_cms_form_field_id');
        })->where('vh_cms_content_types.slug', 'blog')->where('vh_cms_contents.status', 'published');

        if (isset($request->q) && $request->q && $request->q != 'null') {
            $query->where('vh_cms_form_fields.slug', 'title')
                ->where('vh_cms_content_form_fields.content', 'like', "%" . $request->q . "%");
        }

        return $query->orderBy('vh_cms_contents.created_at', 'desc')
            ->select('vh_cms_contents.id', 'vh_cms_contents.created_at')
            ->distinct()->get();

    }

    public function getCategoryResult(Request $request)
    {

        $query = Content::leftJoin('vh_cms_content_types', function ($join) {
            $join->on('vh_cms_content_types.id', 'vh_cms_contents.vh_cms_content_type_id');
        })->join('vh_cms_content_form_fields', function ($join) {
            $join->on('vh_cms_content_form_fields.vh_cms_content_id', '=', 'vh_cms_contents.id');
        })->join('vh_cms_form_fields', function ($join) {
            $join->on('vh_cms_form_fields.id', '=', 'vh_cms_content_form_fields.vh_cms_form_field_id');
        })->where('vh_cms_content_types.slug', 'blog')->where('vh_cms_contents.status', 'published');

        if (isset($request->category) && $request->category && $request->category != 'all') {
            $query->where('vh_cms_form_fields.slug', 'category')
                ->where('vh_cms_content_form_fields.content', $request->category);
        }

        return $query->orderBy('vh_cms_contents.created_at', 'desc')
            ->select('vh_cms_contents.id', 'vh_cms_contents.created_at')
            ->distinct()->get();

    }

    public function storeForm(Request $request)
    {

        $rules = array(
            'name' => 'required',
            'email' => 'required|email',
            'subject' => 'required',
            'message' => 'required'
        );

        $validator = \Validator::make( $request->all(), $rules);
        if ( $validator->fails() ) {

            $errors             = errorsToArray($validator->errors());

            return back()->with('failed', $errors)->withInput();
        }

        try{
            \Mail::to(env('MAIL_FROM_ADDRESS'))->send(new ContactFormMail($request));

            return back()->with('success', 'Thanks for contacting us!');
        }catch (\Exception $e){
            $errors[]             = $e->getMessage();

            return back()->with('failed', $errors)->withInput();
        }



    }

}
