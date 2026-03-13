<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>

<body>
    <p>{{ $transaction->seller->name }} 様</p>

    <p>
        出品された商品の取引が完了しました。
    </p>

    <p>
        商品名: {{ $transaction->item->name }}<br>
        購入者: {{ $transaction->buyer->name }}<br>
        取引ID: {{ $transaction->id }}
    </p>

    <p>
        取引の詳細はマイページから確認できます。
    </p>
</body>

</html>
