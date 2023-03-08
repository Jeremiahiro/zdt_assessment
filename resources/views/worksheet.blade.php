<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<body>
    <table border="2" align="center">
        <tr>
            <th>Last Name</th>
            <th>First Name</th>
            <th>Total Paid Hours</th>
            <th>Total Regular Hours</th>
            <th>Total Overtime Hours</th>
            <th>Total Doubletime</th>
            <th>Worked on Weekends </th>
            <th>Worked 40 Hours </th>
        </tr>
        @if (isset($records))
        @foreach ($records as $user)
            <tr>
                <td align="left">{{ $user['last_name'] }}</td>
                <td align="left">{{ $user['first_name'] }}</td>
                <td align="center">{{ $user['work_stat']['total_paid_hours'] }}</td>
                <td align="center">{{ $user['work_stat']['total_regular_time'] }}</td>
                <td align="center">{{ $user['work_stat']['total_overtime'] }}</td>
                <td align="center">{{ $user['work_stat']['total_overtime'] }}</td>
                <td align="center">{{ $user['work_stat']['worked_on_weekends'] }}</td>
                <td align="center">{{ $user['work_stat']['worked_upto_40_hours'] ? 'True' : 'False' }}</td>
            </tr>
        @endforeach
        @else
            <tr>
                <td>No Records </td>
            </tr>
        @endif
    </table>
</body>
</html>