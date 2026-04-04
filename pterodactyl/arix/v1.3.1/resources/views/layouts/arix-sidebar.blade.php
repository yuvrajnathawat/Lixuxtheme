<aside class="sidebar">
    <ul>
        <li class="{{ $navbar === 'index' ? 'active' : '' }}">
            <a href="{{ route('admin.arix') }}"><i class="fa fa-home"></i>
                <span class="link-tooltip">General</span>
            </a>
        </li>
        <li class="{{ $navbar === 'layout' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.layout') }}"><i class="fa fa-th-large"></i>
                <span class="link-tooltip">Layout</span>
            </a>
        </li>
        <li class="{{ $navbar === 'components' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.components') }}"><i class="fa fa-cubes"></i>
                <span class="link-tooltip">Components</span>
            </a>
        </li>
        <li class="{{ $navbar === 'styling' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.styling') }}"><i class="fa fa-paint-brush"></i>
                <span class="link-tooltip">Styling</span>
            </a>
        </li>
        <li class="{{ $navbar === 'colors' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.colors') }}"><i class="fa fa-palette"></i>
                <span class="link-tooltip">Colors</span>
            </a>
        </li>
        <li class="{{ $navbar === 'announcement' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.announcement') }}"><i class="fa fa-bullhorn"></i>
                <span class="link-tooltip">Announcement</span>
            </a>
        </li>
        <li class="{{ $navbar === 'meta' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.meta') }}"><i class="fa fa-tags"></i>
                <span class="link-tooltip">Meta</span>
            </a>
        </li>
        <li class="{{ $navbar === 'mail' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.mail') }}"><i class="fa fa-envelope"></i>
                <span class="link-tooltip">Mail</span>
            </a>
        </li>
        <li class="{{ $navbar === 'advanced' ? 'active' : '' }}">
            <a href="{{ route('admin.arix.advanced') }}"><i class="fa fa-cog"></i>
                <span class="link-tooltip">Advanced</span>
            </a>
        </li>
    </ul>
</aside>
