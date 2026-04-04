<aside class="arix-sidebar">
    <ul>
        <li class="{{ $navbar === 'index' ? 'active' : '' }}">
            <a href="{{ route('admin.arix') }}"><i class="fa fa-home"></i> General</a>
        </li>
        <li class="{{ $navbar === 'layout' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.layout') }}"><i class="fa fa-th-large"></i> Layout</a>
        </li>
        <li class="{{ $navbar === 'components' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.components') }}"><i class="fa fa-cubes"></i> Components</a>
        </li>
        <li class="{{ $navbar === 'styling' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.styling') }}"><i class="fa fa-paint-brush"></i> Styling</a>
        </li>
        <li class="{{ $navbar === 'colors' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.colors') }}"><i class="fa fa-palette"></i> Colors</a>
        </li>
        <li class="{{ $navbar === 'announcement' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.announcement') }}"><i class="fa fa-bullhorn"></i> Announcement</a>
        </li>
        <li class="{{ $navbar === 'meta' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.meta') }}"><i class="fa fa-tags"></i> Meta</a>
        </li>
        <li class="{{ $navbar === 'mail' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.mail') }}"><i class="fa fa-envelope"></i> Mail</a>
        </li>
        <li class="{{ $navbar === 'advanced' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.advanced') }}"><i class="fa fa-cog"></i> Advanced</a>
        </li>
    </ul>
</aside>
