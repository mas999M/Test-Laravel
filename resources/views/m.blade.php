<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
</head>
<body>
Products ==
<hr>
@foreach($products as $product)
   <a href="{{route('add' , $product->id)}}">{{$product->name}}</a>
@endforeach

<br><br><br>
<a href="{{route('cart')}}">Cart</a>
<hr>
</body>
</html>
