<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisors</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Supervisors List</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Supervisor Degree</th>
                </tr>
            </thead>
            <tbody>
                @foreach($supervisors as $supervisor)
                    <tr>
                        <td>{{ $supervisor->id }}</td>
                        <td>{{ $supervisor->user->name }}</td>
                        <td>{{ $supervisor->supervisorDgree ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
