{% if !isSet(ucmItem) %}
{% set ucmItem = helper('State::getMark', 'displayUcmItem') %}
{% endif %}

{% if !isSet(commentInstance) %}
{% if !isSet(offset) %}
{% set offset = 0 %}
{% endif %}

    {% set commentInstance = helper('Comment::getInstance', ucmItem.context, ucmItem.id, offset) %}
{% endif %}

{% set allowComment = ucmItem.params.get('allowUserComment', 'N') === 'Y' %}
{% set commentAsGuest = ucmItem.params.get('commentAsGuest', 'N') === 'Y' %}
{% set commentWithEmoji = ucmItem.params.get('commentWithEmoji', 'N') === 'Y' %}
{% set canCommentNow = allowComment AND (user.id OR commentAsGuest) %}
{% set headerInput = commentAsGuest AND !user.id %}

<div class="comments-container" data-reference-context="{{ commentInstance.referenceContext | escape_attr }}"
     data-reference-id="{{ commentInstance.referenceId }}">
    {%- macro showComment(item, canCommentNow, replyFormId) %}
    <article class="uk-comment">
        <header class="uk-comment-header">
            <div class="uk-grid-small uk-flex-middle uk-margin" uk-grid>
                <div class="uk-width-auto">
                    {% set author = item.getAuthor() %}
                    {% if author %}
                        {% set avatar = helper('Image::loadImage', author.getParams().get('avatar')) %}
                        {% if (avatar) %}
                            <img class="uk-comment-avatar uk-border-circle"
                                 src="{{ avatar.getResize(32, 32) }}"
                                 width="40"
                                 height="40" alt="{{ item.userName | escape_attr }}"/>
                        {% else %}
                            <div class="c-avatar">{{ substr(item.userName, 0, 1) }}</div>
                        {% endif %}
                    {% else %}
                        <div class="c-avatar">{{ substr(item.userName, 0, 1) }}</div>
                    {% endif %}
                </div>
                <div class="uk-width-expand">
                    <h4 class="uk-comment-title uk-margin-remove">
                        <a class="uk-link-reset" href="#">{{ item.userName }}</a>
                    </h4>
                    <ul class="uk-text-meta uk-grid-small uk-grid-divider" uk-grid>
                        <li class="uk-first-column">
                            <span>{{ helper('Date::relative', item.createdAt) }}</span>
                        </li>
                        {% if canCommentNow %}
                            <li>
                                <a href="#comment-form{{ replyFormId }}"
                                   data-target-author="{{ item.userName | escape_attr }}">
                                    {{ _('reply') }}
                                </a>
                            </li>
                        {% endif %}
                    </ul>
                </div>
            </div>
        </header>
        <div class="uk-comment-body">
            <p>{{ item.userComment }}</p>
        </div>
    </article>
    {% endmacro %}
    {%- macro commentForm (id, type, headerInput, commentWidthEmoji) %}
        <div class="uk-background-muted uk-padding-small comment-form-container uk-margin uk-hidden"
             id="comment-form{{ id }}">
            {% if (headerInput) %}
                <div class="uk-grid-small uk-margin uk-child-width-1-2@s" uk-grid>
                    <div>
                        <input class="uk-input" name="userName" type="text"
                               placeholder="{{ _('enter-your-name') | escape_attr }}"/>
                    </div>
                    <div>
                        <input class="uk-input" name="userEmail" type="email"
                               placeholder="{{ _('enter-your-email-address') | escape_attr }}"/>
                    </div>
                </div>
            {% endif %}
            <div class="uk-margin">
                <textarea class="uk-textarea{{ commentWidthEmoji ? ' input-emoji' : '' }}" name="userComment" rows="2"
                          cols="15"
                          autocomplete="off"></textarea>
            </div>
            <div class="uk-flex uk-flex-right">
                <a class="post-comment uk-button uk-button-primary uk-button-small"
                   data-type="{{ type }}" data-parent-id="{{ type === 'reply' ? id : 0 }}">
                    {{ _('post-your-comment') }}
                </a>
                <a class="uk-button uk-button-danger uk-margin-small-left uk-button-small close"
                   href="#comment-form{{ id }}">
                    {{ _('close') }}
                </a>
            </div>
        </div>
    {% endmacro %}
    {% set length = commentInstance.items | length %}
    {% if length %}
        <hr class="uk-divider-icon"/>
        <div class="comments uk-margin">
            <ul class="uk-comment-list comment-list-container" data-length="{{ length }}"
                data-total-items="{{ commentInstance.totalItems }}">
                {% for item in commentInstance.items %}
                    <li data-comment-id="{{ item.id }}">
                        {{ showComment(item, canCommentNow, item.id) }}
                        {% set repliesCount = item.replies.count() %}
                        {% if (repliesCount > 0) %}
                            <div class="uk-text-right uk-link-toggle">
                                <a class="uk-text-small uk-link-muted"
                                   data-show-text="{{ helper('Text::plural', 'view-more-replies', repliesCount, ['count': repliesCount]) | escape_attr }}"
                                   data-hide-text="{{ helper('Text::plural', 'hide-replies', repliesCount, ['count': repliesCount]) | escape_attr }}"
                                   href="#replies-4-{{ item.id }}">
                                    {{ helper('Text::plural', 'view-more-replies', repliesCount, ['count': repliesCount]) }}
                                </a>
                            </div>
                            <ul id="replies-4-{{ item.id }}" hidden>
                                {% for reply in item.replies %}
                                    <li data-comment-id="{{ reply.id }}">
                                        {{ showComment(reply, canCommentNow, item.id) }}
                                    </li>
                                {% endfor %}
                            </ul>
                        {% endif %}
                        {{ commentForm(item.id, 'reply', headerInput, commentWithEmoji) }}
                    </li>
                {% endfor %}
                {% if commentInstance.totalItems > length %}
                    <li>
                        <a class="uk-heading-line uk-width-1-1 uk-inline uk-text-center uk-link-reset view-more">
                            <span class="uk-button uk-button-small uk-background-muted">{{ _('view-more') }}</span>
                        </a>
                    </li>
                {% endif %}
            </ul>
        </div>
    {% endif %}

    {% if allowComment %}
        <div class="uk-margin uk-text-right">
            {% if canCommentNow %}
                <a class="uk-link-muted" href="#comment-form{{ commentInstance.referenceId }}">
                    {{ '<span uk-icon="pencil"></span>' ~ ' ' ~ _('write-comment-for-this-post') }}
                </a>
            {% else %}
                <a class="uk-link-muted"
                   href="{{ helper('Uri::route', 'user/account?forward=' ~ helper('Uri::getInstance') | url_encode ) }}"
                   uk-toggle>
                    {{ '<span uk-icon="user"></span>' ~ ' ' ~ _('login-to-post-comment') }}
                </a>
            {% endif %}
        </div>
        {{ commentForm(commentInstance.referenceId, 'comment', headerInput, commentWithEmoji) }}
    {% endif %}

    {% if commentWithEmoji %}
        {{ helper('Asset::addFiles', ['comment.css', 'emoji.js', 'comment.js']) | void }}
    {% else %}
        {{ helper('Asset::addFiles', ['comment.css', 'comment.js']) | void }}
    {% endif %}
</div>