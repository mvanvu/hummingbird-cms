<div class="uk-section uk-section-small">
    <div class="uk-container uk-background-default uk-padding uk-box-shadow-small">
        <main id="main-content">
            {% if isHome() %}
                <article class="uk-article uk-text-center">
                    <h1 class="uk-article-title">Welcome to Hummingbird CMS</h1>
                    <p class="uk-article-meta">
                        The powerful CMS built on Phalcon v4, UIkit v3, Php-form...
                    </p>
                    <p class="uk-text-emphasis">
                        Using <em class="uk-text-emphasis">admin/admin</em> to login to the admin <a href="{{ route(cmsConfig['adminPrefix']) }}" target="_blank">{{ route(cmsConfig['adminPrefix']) }}</a>
                        to manage your site
                    </p>
                    <p class="uk-text-emphasis">
                        Browse the <a href="{{ route(cmsConfig['adminPrefix'] ~ '/template/index') }}" target="_blank">admin templates</a>
                        to manage your site templates
                    </p>
                    <p class="uk-text-emphasis">
                        Browse the <a href="{{ route(cmsConfig['adminPrefix'] ~ '/plugin/index') }}" target="_blank">admin plugins</a>
                        to manage your plugins
                    </p>
                    <p class="uk-text-emphasis">
                        Browse the official <a href="https://github.com/mvanvu/hummingbird-cms-post" target="_blank">Post plugin</a>
                        to understand how to make a plugin for the Hummingbird CMS
                    </p>
                </article>
            {% else %}
                {{ content() }}
            {% endif %}
        </main>
    </div>
</div>