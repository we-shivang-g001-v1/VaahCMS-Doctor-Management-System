<div class="container">
    <div class="section">
        @if (Session::get('failed') && count(Session::get('failed')) > 0)
            <article class="message is-danger">
                <div class="message-body">
                    <ul>
                        @foreach (Session::get('failed') as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endif
        @if (Session::get('success'))
            <article class="message is-info">
                <div class="message-body">
                    <strong>{{ Session::get('success') }}</strong>
                </div>
            </article>
        @endif

                <form method="post" action="{{url('/ajax/store-form')}}">
                    <p class="is-size-2 has-text-weight-bold">{!! get_field($data, 'headline', 'header', 'template') !!}</p>

                    <br/>
                        @csrf
                    <div class="field">
                        <label class="label">Name</label>
                        <div class="control">
                            <input class="input" value="{{ old('name') }}" name="name" type="text" placeholder="Name">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Email</label>
                        <div class="control">
                            <input class="input" value="{{ old('email') }}" name="email" type="email" placeholder="xxx@example.com">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Subject</label>
                        <div class="control">
                            <input class="input" value="{{ old('subject') }}" name="subject" type="text" placeholder="Subject">
                        </div>
                    </div>

                    <div class="field">
                        <label class="label">Message</label>
                        <div class="control">
                            <textarea name="message" class="textarea" placeholder="Message">{{ old('message') }}</textarea>
                        </div>
                    </div>


                    <div class="field is-grouped">
                        <div class="control">
                            <button  class="button is-link">Submit</button>
                        </div>
                    </div>
                    <br/>

                </form>

    </div>
</div>
