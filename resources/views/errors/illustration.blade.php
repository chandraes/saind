<svg viewBox="0 0 1200 320" preserveAspectRatio="xMidYMax meet" aria-hidden="true" focusable="false">
    <path d="M80 268H1100" stroke="#a88f4e" stroke-opacity=".38" stroke-width="2" />
    <path d="M80 277H1100" stroke="#dae4df" stroke-opacity=".1" stroke-width="1" />
    @if ($page['scene'] === 'missing')
        <path d="M895 268h170" stroke="#101c25" stroke-width="5" stroke-dasharray="13 11" />
    @endif
    <g class="extras">
        @if (in_array($page['scene'], ['checkpoint', 'payment', 'expired'], true))
            @include('errors.gate', ['page' => $page])
        @else
            @switch($page['scene'])
                @case('barrier')
<g transform="translate(875 197)">
    <path d="M-67 62V-36H67V62" fill="none" stroke="#65777c" stroke-width="5"/>
    <path d="M-75-34H75" stroke="#c39c48" stroke-width="14"/>
    <path d="m-64-40 12 13m20-13 12 13m20-13 12 13m20-13 12 13m20-13 12 13" stroke="#273740" stroke-width="9"/>
    <circle cy="-71" r="21" class="svg-frame"/>
    <path d="M-12-71H12" stroke="#d9ba6e" stroke-width="4"/>
</g>
                    @break
                @case('missing')
<g transform="translate(902 210)">
    <path d="M0 54V-70" stroke="#74817e" stroke-width="4"/>
    <path d="M-70-76H54l19 19-19 19H-70Z" class="svg-frame"/>
    <text x="-1" y="-50" text-anchor="middle" fill="#d9c590" font-family="Arial" font-size="20">?</text>
    <path d="m-48 25 10 15-13 10 12 13" fill="none" stroke="#788080" stroke-width="2"/>
</g>
                    @break
                @case('queue')
<g transform="translate(915 172)">
    <path d="M0 87V-40" stroke="#566c72" stroke-width="4"/>
    <rect x="-16" y="-80" width="32" height="90" rx="7" class="svg-frame"/>
    <circle cy="-59" r="8" fill="#c39845"/>
    <circle cy="-33" r="8" fill="#796535"/>
    <circle cy="-7" r="8" fill="#324244"/>
</g>
                    @break
                @case('service')
<g transform="translate(480 255)">
    <path d="m-13 0 9-34h8l9 34Z" fill="#b78c3b" stroke="#d9bc78"/>
    <path d="M-8-14H8" stroke="#d2d3bf" stroke-width="6"/>
    <path d="M-19 3h38" stroke="#26383e" stroke-width="6"/>
</g>
<g transform="translate(848 255)">
    <path d="m-13 0 9-34h8l9 34Z" fill="#b78c3b" stroke="#d9bc78"/>
    <path d="M-8-14H8" stroke="#d2d3bf" stroke-width="6"/>
    <path d="M-19 3h38" stroke="#26383e" stroke-width="6"/>
</g>
<g class="mechanic" transform="translate(747 245)">
    <path d="M-23 12h29v-15h-29Z" class="svg-metal"/>
    <path d="M-18-3v-5h17v5" class="svg-line"/>
    <circle cx="18" cy="-45" r="10" fill="#89938d"/>
    <path d="M7-49h23v-4l-5-4H12Z" fill="#be9846"/>
    <path d="m12-33-6 25 24 0-5-25Z" fill="#546d77" stroke="#8c9b97"/>
    <path d="M13-29h11m-10 7h10" stroke="#c0a359" stroke-width="2"/>
    <path d="M12-8 8 12M26-8l8 19" stroke="#354d58" stroke-width="7"/>
    <g class="repair-arm">
        <path d="M13-28-7-34-24-24" fill="none" stroke="#8d9d9e" stroke-width="6" stroke-linecap="round"/>
        <path d="m-27-28 8 8m-5-11-5 7 6 6 8-5" fill="none" stroke="#c0c8be" stroke-width="2"/>
    </g>
</g>
                    @break
                @case('breakdown')
<g transform="translate(797 230)">
    <path d="m0-40 28 50H-28Z" class="svg-alert"/>
    <path d="M0-28v19M0-1v1" stroke="#263740" stroke-width="4"/>
</g>
                    @break
            @endswitch
        @endif
    </g>
    @if ($page['scene'] === 'queue')
        <g transform="translate(285 235) scale(.88)" opacity=".55">@include('errors.truck')</g>
        <g transform="translate(484 235) scale(.94)" opacity=".75">@include('errors.truck')</g>
    @endif
    <g class="active-truck" transform="translate(650 235)">
        @include('errors.truck')
        @if (in_array($page['scene'], ['service', 'breakdown'], true))
            <g class="repair-parts">
                <path d="M79-14 69-41l-3 1 8 27Z" fill="#b49a58" stroke="#dcbe75" />
                <path d="M78-16H66v-10h7" fill="#283d47" stroke="#60767d" />
            </g>
        @endif
        @if ($page['scene'] === 'service')
            <path d="m35 36 8-20h7l9 20Z" fill="#8b7546" stroke="#c2ae7a" />
            <path d="M32 37h31" stroke="#5d7078" stroke-width="3" />
        @elseif ($page['scene'] === 'breakdown')
            <g class="smoke"><path d="m74-47 4-8-6-8 4-9M87-44l5-10-7-8 4-10" class="motion-line" /></g>
        @endif
    </g>
    <text x="600" y="310" text-anchor="middle" class="site-note">{{ $page['note'] }}</text>
</svg>
