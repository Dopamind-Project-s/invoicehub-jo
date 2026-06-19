@props(['items' => []])

@if($items !== [])
    <nav aria-label="breadcrumb" class="app-breadcrumb">
        <ol class="breadcrumb mb-0">
            @foreach($items as $item)
                <li class="breadcrumb-item @if($loop->last) active @endif" @if($loop->last) aria-current="page" @endif>
                    @if(! $loop->last && isset($item['url']))
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    @else
                        {{ $item['label'] }}
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
