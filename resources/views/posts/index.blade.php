<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Posts</title>
    <style>
        :root { color-scheme: light dark; }
        body { font-family: system-ui, sans-serif; max-width: 42rem; margin: 0 auto; padding: 2rem 1rem; line-height: 1.6; }
        h1 { margin-bottom: 2rem; }
        article { padding: 1.25rem 0; border-top: 1px solid #8884; }
        article h2 { margin: 0 0 .25rem; font-size: 1.25rem; }
        .meta { font-size: .85rem; opacity: .65; margin-bottom: .75rem; }
        .body p { margin: 0 0 .75rem; }
        .empty { text-align: center; padding: 4rem 1rem; border: 2px dashed #8886; border-radius: .75rem; opacity: .8; }
        .empty h2 { margin: 0 0 .5rem; }
    </style>
</head>
<body>
    <h1>Posts</h1>

    @forelse ($posts as $post)
        <article>
            <h2>{{ $post->title }}</h2>
            <div class="meta">
                {{ $post->author }} · <time datetime="{{ $post->published_at?->toIso8601String() }}">{{ $post->published_at?->format('M j, Y') }}</time>
            </div>
            <div class="body">
                @foreach (preg_split('/\n{2,}/', $post->body) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>
        </article>
    @empty
        <div class="empty">
            <h2>No hay posts todavía</h2>
            <p>Cuando se publique el primero, aparecerá aquí.</p>
        </div>
    @endforelse
</body>
</html>
