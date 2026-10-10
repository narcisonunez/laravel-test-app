<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Posts</title>
    <style>
        :root {
            color-scheme: light dark;
            --bg: #faf6f0;
            --surface: #ffffff;
            --text: #2b2118;
            --muted: #7a6a5c;
            --accent: #d9480f;
            --border: #eadfd3;
        }
        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #1a1512;
                --surface: #241d18;
                --text: #f3ebe2;
                --muted: #a8988a;
                --accent: #ff7a3d;
                --border: #3a2f27;
            }
        }
        body { font-family: Georgia, 'Times New Roman', serif; background: var(--bg); color: var(--text); max-width: 44rem; margin: 0 auto; padding: 3rem 1rem; line-height: 1.7; }
        h1 { margin: 0 0 2.5rem; font-size: 2.5rem; letter-spacing: -.02em; border-bottom: 4px solid var(--accent); display: inline-block; padding-bottom: .25rem; }
        article { background: var(--surface); border: 1px solid var(--border); border-left: 4px solid var(--accent); border-radius: .75rem; padding: 1.5rem 1.75rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px #0000000d; }
        article h2 { margin: 0 0 .25rem; font-size: 1.4rem; }
        .meta { font-family: system-ui, sans-serif; font-size: .8rem; text-transform: uppercase; letter-spacing: .06em; color: var(--muted); margin-bottom: 1rem; }
        .body p { margin: 0 0 .75rem; }
        .empty { text-align: center; padding: 4rem 1rem; background: var(--surface); border: 2px dashed var(--accent); border-radius: .75rem; }
        .empty h2 { margin: 0 0 .5rem; color: var(--accent); }
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
