@extends("layout.default")

@section("title", "E-com - Home")

@section("content")
<main class="container" style="max-width:900px">
<section>
<div class="row">
    @foreach($products as $product)
    <div class="col-12 col-md-6 col-lg-3">
        <div class="card shadow-sm p-2">
            
            <img src="{{ $product->image }}" class="img-fluid" alt="{{ $product->name }}">
            
            <div class="mt-2">
                <a href="{{route("products.details", $product->slug)}}">
                    {{$product->name}}
                </a>
                <br>
                <span>{{ $product->price }}</span>
            </div>

        </div>
    </div>
    @endforeach
    <div>
        {{$products->links()}}
</div>
</div>
</section>
</main>
@endsection