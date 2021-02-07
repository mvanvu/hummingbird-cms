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

            <div class="uk-navbar-right nav-overlay">
                <a class="uk-navbar-toggle uk-visible@m" uk-search-icon
                   uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"></a>
            </div>
            <div class="nav-overlay uk-navbar-left uk-flex-1" hidden>
                <div class="uk-navbar-item uk-width-expand">
                    <form class="uk-search uk-search-navbar uk-width-1-1" action="{{ route('search') }}" method="get">
                        <input class="uk-search-input" name="q" type="search"
                               value="{{ request.get('q', ['trim', 'string'], '') }}"
                               placeholder="{{ _('search-hint') | escape_attr }}">
                    </form>
                </div>
                <a class="uk-navbar-toggle" uk-close
                   uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"></a>
            </div>
        </nav>
    </div>
</header>