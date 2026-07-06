<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Todo') }}</title>
    @php
        $iconPath = auth()->user()?->setting('icon');
        $iconUrl = $iconPath ? Illuminate\Support\Facades\Storage::disk('public')->url($iconPath) : null;
    @endphp
    @if ($iconUrl)
        <link rel="icon" type="image/png" href="{{ $iconUrl }}">
        <link rel="apple-touch-icon" href="{{ $iconUrl }}">
    @else
        <link rel="icon" type="image/png" href="favicon.png">
        <link rel="apple-touch-icon" href="/favicon.png">
    @endif
    <style>
        html {
            touch-action: manipulation;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #fdf6e3;
            margin: 0;
            padding: 20px;
            color: #586e75;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            text-align: center;
            margin: 0 0 20px 0;
            color: #586e75;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .todo-list {
            list-style: none;
        }

        .todo-item {
            background-color: #eee8d5;
            border-radius: 8px;
            padding: 24px 16px;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
            position: relative;
            overflow: hidden;
            width: 100%;
        }

        .todo-progress-bar {
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            background-color: rgba(133, 153, 0, 0.12);
            z-index: 0;
            border-radius: 8px 0 0 8px;
            width: 0%;
        }

        .todo-progress-bar.animate {
            transition: none;
        }

        .todo-icon {
            margin-right: 12px;
            color: #93a1a1;
            font-size: 1.2rem;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            flex-shrink: 0;
            position: relative;
            z-index: 1;
        }

        .todo-item-content {
            font-size: 1.2rem;
            color: #586e75;
            font-weight: 500;
            flex: 1;
            padding-right: 80px;
            position: relative;
            z-index: 1;
        }

        .todo-item-meta {
            font-size: 0.85rem;
            color: #839496;
            margin-top: 4px;
            font-weight: 400;
        }

        .tick-icon {
            background-color: #859900;
            color: #fdf6e3;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 1.5rem;
            font-weight: bold;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            position: absolute;
            right: 16px;
            z-index: 3;
            outline: none;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }

        .tick-icon:hover {
            background-color: #657b00;
        }

        .confirm-text {
            background-color: #268bd2;
            color: #fdf6e3;
            padding: 0 20px;
            border-radius: 0 8px 8px 0;
            font-weight: 600;
            cursor: pointer;
            opacity: 0;
            pointer-events: none;
            transform: translateX(40px);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            font-size: 1rem;
            outline: none;
            -webkit-tap-highlight-color: transparent;
            user-select: none;
        }

        .confirm-text:hover {
            background-color: #1d6fa5;
        }

        .todo-item.slide-active .tick-icon {
            opacity: 0;
            transform: translateX(-100px);
            pointer-events: none;
        }

        .todo-item.slide-active .confirm-text {
            opacity: 1;
            transform: translateX(0);
            pointer-events: auto;
        }

        .fireworks-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            pointer-events: none;
            overflow: hidden;
        }

        .firework-particle {
            position: absolute;
            border-radius: 50%;
            animation: firework-explosion 0.8s forwards;
            box-shadow: 0 0 4px currentColor;
            filter: blur(0.5px);
        }

        @keyframes firework-explosion {
            0% {
                transform: translate(0, 0) scale(0.2);
                opacity: 1;
            }
            50% {
                opacity: 0.8;
            }
            100% {
                transform: translate(var(--dx), var(--dy)) scale(var(--final-scale));
                opacity: 0;
            }
        }

        .firework-sparkle {
            position: absolute;
            background: radial-gradient(circle at center, rgba(255,255,255,0.8) 0%, transparent 70%);
            border-radius: 50%;
            animation: sparkle-flash 0.4s ease-out forwards;
            opacity: 0;
        }

        @keyframes sparkle-flash {
            0% {
                transform: scale(0.1);
                opacity: 0;
            }
            50% {
                transform: scale(0.6);
                opacity: 0.9;
            }
            100% {
                transform: scale(0.8);
                opacity: 0;
            }
        }

        .todo-item.completed {
            background-color: #eee8d5 !important;
            opacity: 0.7 !important;
        }

        .todo-item.completed .todo-item-content {
            color: #657b83 !important;
        }

        .todo-item.completed .todo-icon {
            color: #859900 !important;
            transform: scale(1.2) !important;
            transition: all 0.3s ease !important;
        }

        .habit-completed-count {
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            display: flex;
            align-items: center;
            padding: 0 16px;
            z-index: 1;
            user-select: none;
            color: #93a1a1;
            opacity: 0.5;
        }

        .habit-completed-count .count-inner {
            display: flex;
            align-items: baseline;
        }

        .habit-completed-count .x-symbol {
            font-size: 1.8rem;
            font-weight: 600;
        }

        .habit-completed-count .count-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }

        .progress-bar {
            height: 4px;
            background-color: #eee8d5;
            border-radius: 2px;
            margin-top: 8px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background-color: #268bd2;
            border-radius: 2px;
            transition: width 0.3s ease;
        }

        .progress-fill.complete {
            background-color: #859900;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #839496;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 16px;
        }
    </style>
    @livewireStyles
</head>
<body>
    {{ $slot }}
    @livewireScripts
    <script src="/js/pulltorefresh.js"></script>
    <script>
        const ptr = PullToRefresh.init({
            mainElement: 'body',
            onRefresh() {
                window.location.reload();
            }
        });
    </script>
</body>
</html>
