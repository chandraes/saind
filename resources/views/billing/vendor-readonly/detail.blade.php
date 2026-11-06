<article aria-label="{{ $title }}"><h3 class="h5 mb-3">{{ $title }}</h3>
    <dl class="row mb-0">
        @foreach ($fields as $label => $value)
            <dt class="col-sm-5 py-2 border-bottom">{{ $label }}</dt><dd class="col-sm-7 py-2 border-bottom text-break mb-0">{{ $value === null || $value === '' ? '—' : $value }}</dd>
        @endforeach
    </dl>
</article>
