<header class="uk-background-default" id="header" uk-sticky="show-on-up: true; animation: uk-animation-fade; media: @l">
    <div class="uk-container">
        <nav id="navbar" uk-navbar="mode: click;">
            <div class="uk-navbar-left nav-overlay uk-visible@m">
                {% set topAMenu = menu('TopA', 'Navbar') %}
                {% if topAMenu is not empty %}
                    <div class="top-a-menu">
                        {{ topAMenu }}
                    </div>
                {% endif %}
                {{ widget('TopA', 'Raw') }}
            </div>
            <div class="uk-navbar-center nav-overlay">
                <a class="uk-navbar-item uk-logo" href="{{ route('/') }}" title="Logo">
                    <img src="{{ constant('ROOT_URI') ~ '/assets/images/logo.png' }}" width="55" height="55"
                         alt="Logo"/>
                    <span class="uk-text-small uk-text-muted uk-margin-small-left">
                        HummingBird
                    </span>
                </a>
            </div>
            <div class="uk-navbar-right nav-overlay">
                <a class="uk-navbar-toggle uk-visible@m" uk-search-icon
                   uk-toggle="target: .nav-overlay; animation: uk-animation-fade" href="#"></a>

                {{ widget('TopB', 'Raw') }}
                {% set topBMenu = menu('TopB', 'Navbar') %}
                {% if topBMenu is not empty %}
                    <div class="top-b-menu">
                        {{ topBMenu }}
                    </div>
                {% endif %}
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
{% set mainMenu = menu('MainMenu', 'Navbar') %}
{% if mainMenu is not empty %}
    <div class="uk-navbar-container uk-navbar-transparent uk-background-default uk-visible@s">
        <div class="uk-container uk-background-default" style="border-top: 1px solid rgba(0,0,0,0.075)" uk-navbar>
            <nav id="main-menu">
                <div class="uk-navbar-center">
                    {{ mainMenu }}
                </div>
            </nav>
        </div>
    </div>
{% endif %}