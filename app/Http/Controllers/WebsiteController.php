<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Enquiry;
use App\Models\Gallery;
use App\Models\MainProduct;
use App\Models\SubProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WebsiteController extends Controller
{
    public function home()
    {
        $subProducts = Cache::remember('website.home.sub_products', now()->addMinutes(10), function () {
            return SubProduct::where('status', 1)
                ->select(['id', 'main_product_id', 'name', 'price', 'description', 'url', 'image', 'status'])
                ->with(['mainproduct:id,product_name,image,status'])
                ->get();
        });

        $blogs = Cache::remember('website.home.blogs', now()->addMinutes(10), function () {
            return Blog::where('status', 1)
                ->select(['id', 'title', 'url', 'image', 'content', 'created_at', 'status'])
                ->latest('id')
                ->limit(3)
                ->get();
        });

        return view('website.home', compact('subProducts', 'blogs'));
    }
    public function about()
    {
        return view('website.about');
    }
    public function servicelist(Request $request)
    {
        return view('website.servicelist');
    }
    public function productlist(Request $request)
    {
        $mainProducts = Cache::remember('website.productlist.main_products', now()->addMinutes(10), function () {
            return MainProduct::where('status', 1)
                ->select(['id', 'product_name', 'description', 'image', 'status'])
                ->get();
        });

        $subProducts = Cache::remember('website.productlist.sub_products', now()->addMinutes(10), function () {
            return SubProduct::where('status', 1)
                ->select(['id', 'main_product_id', 'name', 'price', 'description', 'url', 'image', 'status'])
                ->with(['mainproduct:id,product_name,image,status'])
                ->get();
        });

        // 3. Return the view and pass BOTH variables to it
        return view('website.productlist', [
            'mainProducts' => $mainProducts,
            'products' => $subProducts // Use 'products' to match your existing @foreach
        ]);
    }
    public function firehydrantinstallation()
    {
        return view('website.fire-hydrant-installation');
    }
    public function firehydrantsysteminstallationservice()
    {
        return view('website.fire-hydrant-system-installation-service');
    }
    public function signboardinstallationservice()
    {
        return view('website.sign-board-installation-service');
    }
    public function fireextinguisherinstallationservice(Request $request)
    {
        return view('website.fire-extinguisher-installation-service');
    }
    public function productdetails($url)
    {
        $subproduct = SubProduct::where('url', $url)
            ->with('mainproduct')
            ->where('status', 1)
            ->first();

        if (!$subproduct) {
            abort(404);
        }

        // ✅ Get multiple related products (collection)
        $relatedproducts = SubProduct::where('main_product_id', $subproduct->main_product_id)
            ->where('id', '!=', $subproduct->id) // Avoid showing the same product
            ->with('mainproduct')
            ->where('status', 1)
            ->limit(4)
            ->get();

        // ✅ Fallback: get other products if none found
        if ($relatedproducts->isEmpty()) {
            $relatedproducts = SubProduct::where('id', '!=', $subproduct->id)
                ->with('mainproduct')
                ->where('status', 1)
                ->limit(4)
                ->get();
        }

        return view('website.productdetails', compact('subproduct', 'relatedproducts'));
    }
    public function websiteblog()
    {
        $blogs = Cache::remember('website.blog.list', now()->addMinutes(10), function () {
            return Blog::where('status', 1)
                ->select(['id', 'title', 'url', 'image', 'content', 'created_at', 'status'])
                ->latest('id')
                ->get();
        });

        return view('website.blog', compact('blogs'));
    }
    public function blogdetails($url)
    {
        $blog = Blog::where('status', 1)->where('url', $url)->first();
        if (!$blog) {
            abort(404);
        }
        $recent_posts = Blog::where('status', 1)->where('id', '!=', $blog->id)->limit(4)->get();
        return view('website.blogdetails', compact('blog', 'recent_posts'));
    }
    public function websitegallery(Request $request)
    {
        $gallerys = Cache::remember('website.gallery.list', now()->addMinutes(10), function () {
            return Gallery::where('status', 1)
                ->select(['id', 'image', 'category_id', 'status'])
                ->with('category:id,category')
                ->get();
        });

        return view('website.gallery', compact('gallerys'));
    }
    public function contact()
    {
        return view('website.contact');
    }
    public function ensuringsafetywithfixedlifeline()
    {
        return view('website.ensuring-safety-with-fixed-lifeline');
    }
    public function insertenquiry(Request $request)
    {
        $productName = $request->input('productName');
        $quantity = $request->input('quantity');
        $name = $request->input('name');
        $phone = $request->input('phone');
        $message = $request->input('message');
        $insertenquiry = Enquiry::create([
            'name' => $name,
            'mobile' => $phone,
            'comments' => $message,
            'product_name' => $productName,
            'quantity' => $quantity,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json([
            'status' => '200',
            'message' => 'Enquiry Added successfuly'
        ]);
    }
    public function enquiry(Request $request)
    {
        $enquirys = Enquiry::where('status', 1)->orderBy('created_at', 'desc')->get();
        return view('products.enquiry', compact('enquirys'));
    }
}
