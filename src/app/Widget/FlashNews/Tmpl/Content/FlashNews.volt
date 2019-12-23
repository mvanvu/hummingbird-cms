{% set inlineJs = '
var
    fnSlider = $(".flash-news [uk-slider]"),
    fnThumb = $(".flash-news .slide-thumb");
    fnSlider.on("itemshow", function (e) {
        e.preventDefault();
        fnThumb.removeClass("active uk-box-shadow-large");
        fnThumb.eq($(e.target).index()).addClass("active uk-box-shadow-large");
    });

    fnThumb.find(".uk-cover-container").on("click", function (e) {
       e.preventDefault();
       UIkit.slider(fnSlider[0]).show($(this).parents(".slide-thumb:eq(0)").index());
    });
' %}

{{ helper('Asset::inlineJs', inlineJs | trim) | void }}
{% set loadedPosts = [] %}
<div class="flash-news">
    <div class="uk-grid-match uk-child-width-1-2@s" uk-grid>
        <div class="uk-width-2-3@m">
            <div uk-slider="center: true; autoplay: true">
                <div class="uk-slider-container uk-position-relative uk-visible-toggle">
                    <ul class="uk-slider-items uk-child-width-1-1 news-slide">
                        {% for post in posts %}
                            {% set loadedPosts[post.id] = [
                                'image': helper('Image::loadImage', post.t('image')),
                                'link': post.link,
                                'title': post.t('title'),
                                'summary': post.summary(),
                                'date': helper('Date::relative', post.createdAt)
                            ] %}
                            {% set post = loadedPosts[post.id] %}
                            <li>
                                <div class="uk-card uk-card-default uk-card-small">
                                    {% if post['image'] is not empty %}
                                        <div class="uk-card-media-top">
                                            <img data-src="{{ post['image'].getResize(800) }}"
                                                 alt="{{ post['title'] | escape_attr }}" uk-img/>
                                        </div>
                                    {% endif %}
                                    <div class="uk-card-body">
                                        <h2 class="uk-text-truncate uk-margin-small-bottom">
                                            <a class="uk-link-reset"
                                               title="{{ post['title'] }}"
                                               href="{{ post['link'] }}">
                                                {{ post['title'] }}
                                            </a>
                                        </h2>
                                        <p class="uk-text-meta uk-margin-remove">
                                            {{ post['summary'] }}
                                        </p>
                                    </div>
                                </div>
                            </li>
                        {% endfor %}
                    </ul>
                    <a class="uk-position-center-left uk-position-small uk-hidden-hover" href="#"
                       uk-slidenav-previous
                       uk-slider-item="previous"></a>
                    <a class="uk-position-center-right uk-position-small uk-hidden-hover" href="#"
                       uk-slidenav-next
                       uk-slider-item="next"></a>
                    <ul class="uk-slider-nav uk-dotnav uk-flex-center uk-margin"></ul>
                </div>
            </div>
        </div>
        <div class="uk-width-1-3@m">
            {% for post in loadedPosts %}
                <div class="uk-card uk-background-muted uk-card-small uk-grid-small slide-thumb"
                     uk-grid>
                    <div class="uk-width-2-3">
                        <div class="uk-padding-small">
                            <a class="uk-text-truncate uk-text-emphasis uk-display-block"
                               href="{{ post['link'] }}"
                               title="{{ post['title'] | escape_attr }}">
                                {{ post['title'] }}
                            </a>
                            <small class="uk-text-meta">
                                {{ post['date'] }}
                            </small>
                            <p class="uk-margin-remove uk-text-truncate uk-text-meta">
                                {{ post['summary'] }}
                            </p>
                        </div>
                    </div>
                    {% if post['image'] is not empty %}
                        <div class="uk-card-media-right uk-cover-container uk-width-1-3" style="cursor: pointer">
                            <img data-src="{{ post['image'].getResize(300, 120) }}"
                                 alt="{{ post['title'] | escape_attr }}" uk-cover uk-img/>
                            <canvas width="300" height="120"></canvas>
                        </div>
                    {% endif %}
                </div>
            {% endfor %}
        </div>
    </div>
</div>