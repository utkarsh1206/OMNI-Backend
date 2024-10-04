<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Program</title>
</head>
<body>
    <h1>Welcome to the Test Program Page</h1>
    <form action="/test" method="POST">
        @csrf
        <label for="name">Name:</label>
        <input type="text" id="name" name="name" required>
        <button type="submit">Submit</button>
    </form>

    @if(session('message'))
        <p>{{ session('message') }}</p>
    @endif
</body>
</html>
