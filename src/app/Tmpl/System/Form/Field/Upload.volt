<div class="upload-container">
    <div class="uk-grid-small uk-flex-middle uk-flex-center uk-flex-wrap{{ files | length ? '' : ' uk-hidden' }}"
         id="{{ field.id }}-files" uk-grid{{ field.multiple  ? ' uk-sortable' : '' }}>
        {% for file in files %}
            {{ partial('Form/Field/UploadedFile', ['file': file]) }}
        {% endfor %}
    </div>
    {{ input }}
    <div class="uk-placeholder uk-text-center" id="{{ field.id }}-upload">
        <span uk-icon="icon: cloud-upload"></span>
        <span class="uk-text-middle">{{ _('media-upload-hint') }}</span>
        <div uk-form-custom>
            <input type="file" accept="{{ field.accept }}"{{ field.multiple ? ' multiple' : '' }}/>
            <span class="uk-link">{{ _('media-upload-select') }}</span>
        </div>
    </div>
    <progress id="{{ field.id }}-progressbar" class="uk-progress" value="0" max="100" hidden></progress>
    <script>
        window.addEventListener('load', function () {
            (function ($) {
                var bar = document.getElementById('{{ field.id }}-progressbar'),
                    multiple = {{ field.multiple ? 'true' : 'false' }},
                    container = $('#{{ field.id }}-files'),
                    select = $('#{{ field.id }}'),
                    updateValue = function () {
                        select.empty();

                        if (multiple) {
                            container.find('[data-file-base]').each(function () {
                                select.append('<option value="' + this.getAttribute('data-file-base') + '" selected></option>');
                            });

                        } else {
                            var el = container.find('[data-file-base]').get(0);

                            if (el.length) {
                                select.append('<option value="' + el.data('fileBase') + '" selected></option>');
                            }
                        }
                    };

                container.on('moved', updateValue);
                container.on('click', 'a.remove', function (e) {
                    e.preventDefault();
                    var p = $(this).parent('[data-file-url]');
                    p.remove();
                    updateValue();
                });

                UIkit.upload('#{{ field.id }}-upload', {

                    url: '{{ route('file/upload') }}',
                    multiple: multiple,
                    params: {
                        encrypted: '{{ encrypted }}',
                        tmpUpload: '{{ field.tmpUpload ? '1' : '0' }}',
                    },

                    beforeSend: function (environment) {
                        environment.headers['X-CSRF-Token'] = document.querySelector('meta[name="csrf"]').getAttribute('content');
                    },

                    loadStart: function (e) {
                        bar.removeAttribute('hidden');
                        bar.max = e.total;
                        bar.value = e.loaded;
                    },

                    progress: function (e) {
                        bar.max = e.total;
                        bar.value = e.loaded;
                    },

                    loadEnd: function (e) {
                        bar.max = e.total;
                        bar.value = e.loaded;
                    },

                    complete: function (response) {
                        var json = JSON.parse(response.responseText);

                        if (json.success) {
                            if (json.data.length) {
                                if (!multiple) {
                                    container.empty();
                                }

                                json.data.forEach(function (data) {
                                    container.append(data.html);
                                });

                                container.removeClass('uk-hidden');
                                updateValue();
                            }
                        } else {
                            UIkit.notification(json.message, {status: 'warning'});
                        }
                    },

                    completeAll: function (response) {
                        setTimeout(function () {
                            bar.setAttribute('hidden', 'hidden');
                        }, 1000);
                    }
                });
            })(_$);
        });
    </script>
</div>