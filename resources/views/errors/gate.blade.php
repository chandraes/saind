<g transform="translate(885 182)">
    <path d="M-33 75V-78h78V75M-45-78h103l-20-15h-66Z" class="svg-frame"/>
    <rect x="-22" y="-65" width="53" height="52" fill="#263c47" stroke="#7b8784"/>
    <g transform="translate(4 30)">@if ($page['scene'] === 'payment')
<rect x="-11" y="-15" width="22" height="30" rx="2" fill="none" stroke="#d3bd80" stroke-width="2"/>
<path d="M-5-7H5M-5 0H5M-5 7H1" class="svg-line"/>
@elseif ($page['scene'] === 'expired')
<circle r="19" fill="none" stroke="#d3bd80" stroke-width="2"/>
<path d="M0-12V0l8 5" class="svg-line"/>
@else
<rect x="-14" y="-3" width="28" height="21" rx="3" fill="none" stroke="#d3bd80" stroke-width="2"/>
<path d="M-8-3v-8a8 8 0 0 1 16 0v8" class="svg-line"/>
@endif</g>
    <rect x="-151" y="-11" width="125" height="9" fill="#ac8b3a" stroke="#d0b96e"/>
    <path d="M-146-11h16l-9 9h-16m45-9h16l-9 9h-16m45-9h16l-9 9h-16" fill="#354046"/>
</g>
