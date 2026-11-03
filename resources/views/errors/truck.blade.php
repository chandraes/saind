<ellipse cx="0" cy="33" rx="89" ry="7" fill="#050b10" opacity=".48"/>
<path d="M-77 6H70l9 10H-77Z" fill="#18262d" stroke="#697273"/>
<path d="M-76 3H68v6H-76Z" fill="#a7853b"/>
<g class="bed">
    <path d="m-75-49 9 45H15l13-45Z" class="svg-metal"/>
    <path d="M-75-49H28" stroke="#bd9846" stroke-width="3"/>
    <path d="m-62-44 6 35m13-35 3 35m17-35 1 35M0-44-3-9" fill="none" stroke="#738080" stroke-width="2"/>
    <g class="load">
        <path d="m-74-50 10-9 12 3 10-9 14 3 10-5 13 9 12-3 13 11Z" class="svg-coal"/>
        <path d="m-55-54 8 2 7-8m11 4 6 5 9-7" fill="none" stroke="#59666b" stroke-width="1"/>
    </g>
</g>
<path d="M31-42h30l17 24V6H30Z" class="svg-amber"/>
<path d="m38-37 20 0 12 18H38Z" fill="#223844" stroke="#667b82"/>
<path d="M59-36v16M37-15h30M38-11v15" fill="none" stroke="#7e6a36"/>
<path d="M28-45h36" stroke="#ddc47a" stroke-width="3"/>
<path d="M26-7v-56h5v56" fill="#46575d"/>
<path d="M24-63h9" stroke="#78858b" stroke-width="2"/>
<path d="M46-47v-4h11v4" fill="#c99138"/>
<path d="M74-11h8v12h-8Z" fill="#7d7c67"/>
<path d="M79-7h6v5h-6" fill="#e2ca85"/>
@foreach ([-51, -16, 56] as $wheelPosition)
<g class="wheel" transform="translate({{ $wheelPosition }} 16)"><circle r="15" fill="#111a20" stroke="#465159" stroke-width="3"/><circle r="8" fill="#ad8839" stroke="#d0ae62"/><circle r="3" fill="#29363b"/><path d="M0-11V-7M0 7v4M7 0h4M-11 0h4" stroke="#7c8889" stroke-width="1.5"/></g>
@endforeach
