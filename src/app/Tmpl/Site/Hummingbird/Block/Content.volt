<div class="uk-section uk-section-small uk-section-default">
    <div class="uk-container">
        {% if home() %}
            <div class="uk-margin">
                {{ widget('FlashNews', 'Raw') }}
            </div>
            <div class="uk-margin">
                {{ widget('Trending', 'HeadingLine') }}
            </div>
            <div class="uk-margin">
                <div uk-grid>
                    <div class="uk-width-2-3@m">
                        <div uk-margin>
                            {{ widget('LatestNews', 'HeadingLine') }}
                        </div>
                    </div>
                    <div class="uk-width-1-3@m">
                        <div uk-margin>
                            {{ widget('Aside', 'HeadingLine') }}
                        </div>
                    </div>
                </div>
            </div>
        {% else %}
            <div uk-grid>
                <div class="uk-width-2-3@m">
                    <main id="main-content">
                        {{ content() }}
                    </main>
                </div>
                <div class="uk-width-1-3@m">
                    <aside id="aside">
                        {{ widget('Aside', 'HeadingLine') }}
                    </aside>
                </div>
            </div>
        {% endif %}
    </div>
</div>