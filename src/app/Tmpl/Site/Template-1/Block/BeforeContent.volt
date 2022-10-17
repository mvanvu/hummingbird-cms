{% set params = template.getParams() %}
<header id="header" uk-sticky="show-on-up: true; animation: uk-animation-fade; media: @l">
    <div class="uk-container">
        <nav id="navbar" uk-navbar="mode: click">
            <div class="uk-navbar-left nav-overlay">
                <a class="uk-navbar-item uk-logo" href="{{ route('/') }}" title="Logo">
                    <img src="{{ public('images/logo.png') }}" width="55" height="55"
                         alt="Logo"/>
                    <span class="uk-text-small uk-text-muted uk-margin-small-left">
                        Hummingbird CMS
                    </span>
                </a>
            </div>

            <div class="uk-navbar-left nav-overlay">
                {{ menu('MainMenu', 'Navbar') }}
            </div>

            <div class="nav-overlay uk-navbar-right">
                <form class="uk-search uk-search-default uk-width-medium" action="{{ route('search') }}" method="get">
                    <span uk-search-icon></span>
                    <input class="uk-search-input uk-border-pill" name="q" type="search"
                           value="{{ request.get('q', ['trim', 'string'], '') }}"
                           placeholder="{{ _('search-hint') | escape_attr }}">
                </form>
            </div>
        </nav>
    </div>
</header>