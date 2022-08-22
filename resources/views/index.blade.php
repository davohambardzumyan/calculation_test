<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
</head>
<body>

<form method="post" enctype="multipart/form-data">
    @csrf
    <input type="file" name="csv_file" required/>
    @if ($errors->has('csv_file'))
        <span class="text-danger">{{ $errors->first('csv_file') }}</span>
    @endif
    <input type="submit" name="submit" value="Calculate"/>
</form>
</body>
</html>


