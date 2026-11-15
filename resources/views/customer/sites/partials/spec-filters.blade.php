@php
    $languages = $languages ?? [];
    $countries = $countries ?? [];
    $categories = $categories ?? [];
    $defaultNiche = $defaultNiche ?? '';
    $defaultType = $defaultType ?? '';
    $defaultDr = $defaultDr ?? '';
    $defaultCountry = $defaultCountry ?? '';
@endphp
<select data-filter="as" aria-label="Minimum AS">
    <option value="">AS</option>
    @foreach ([20, 30, 40, 50, 60] as $min)
        <option value="{{ $min }}">AS {{ $min }}+</option>
    @endforeach
</select>
<select data-filter="da" aria-label="Minimum DA">
    <option value="">DA</option>
    @foreach ([20, 30, 40, 50, 60] as $min)
        <option value="{{ $min }}">DA {{ $min }}+</option>
    @endforeach
</select>
<select data-filter="dr" aria-label="Minimum DR">
    <option value="">DR</option>
    @foreach ([20, 30, 40, 50, 60, 70] as $min)
        <option value="{{ $min }}" @selected((string) $defaultDr === (string) $min)>DR {{ $min }}+</option>
    @endforeach
</select>
<select data-filter="pa" aria-label="Minimum PA">
    <option value="">PA</option>
    @foreach ([20, 30, 40, 50, 60] as $min)
        <option value="{{ $min }}">PA {{ $min }}+</option>
    @endforeach
</select>
<select data-filter="tf" aria-label="Minimum TF">
    <option value="">TF</option>
    @foreach ([20, 30, 40, 50, 60] as $min)
        <option value="{{ $min }}">TF {{ $min }}+</option>
    @endforeach
</select>
<select data-filter="niche" aria-label="Search by niche">
    <option value="">Niche</option>
    @foreach ($categories as $name => $slug)
        <option value="{{ $name }}" @selected($defaultNiche === $name)>{{ $name }}</option>
    @endforeach
</select>
<select data-filter="price" aria-label="Price range">
    <option value="">Price range</option>
    @foreach ([100, 250, 500, 1000] as $max)
        <option value="{{ $max }}">Up to ${{ number_format($max) }}</option>
    @endforeach
</select>
<select data-filter="traffic" aria-label="Minimum Ahrefs traffic">
    <option value="">Ahrefs traffic</option>
    @foreach ([1000 => '1K+', 5000 => '5K+', 10000 => '10K+', 50000 => '50K+'] as $min => $label)
        <option value="{{ $min }}">Ahrefs {{ $label }}</option>
    @endforeach
</select>
<select data-filter="semrush" aria-label="Minimum Semrush traffic">
    <option value="">Semrush traffic</option>
    @foreach ([1000 => '1K+', 5000 => '5K+', 10000 => '10K+', 50000 => '50K+'] as $min => $label)
        <option value="{{ $min }}">Semrush {{ $label }}</option>
    @endforeach
</select>
<select data-filter="language" aria-label="Language">
    <option value="">Language</option>
    @foreach ($languages as $language)
        <option value="{{ $language }}">{{ $language }}</option>
    @endforeach
</select>
<select data-filter="sports" aria-label="Sports/Gaming allowed?">
    <option value="">Sports/Gaming allowed?</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
</select>
<select data-filter="pharmacy" aria-label="Pharmacy allowed?">
    <option value="">Pharmacy allowed?</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
</select>
<select data-filter="crypto" aria-label="Crypto allowed?">
    <option value="">Crypto allowed?</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
</select>
<select data-filter="backlinks" aria-label="Number of backlinks">
    <option value="">No. of backlinks</option>
    @foreach ([10, 50, 100, 500, 1000] as $min)
        <option value="{{ $min }}">{{ number_format($min) }}+</option>
    @endforeach
</select>
<select data-filter="referring" aria-label="Number of referring domains">
    <option value="">No. of referring domains</option>
    @foreach ([10, 50, 100, 500, 1000] as $min)
        <option value="{{ $min }}">{{ number_format($min) }}+</option>
    @endforeach
</select>
<select data-filter="spam" aria-label="Spam Score">
    <option value="">Spam Score</option>
    @foreach ([5 => 'SS up to 5', 10 => 'SS up to 10', 20 => 'SS up to 20', 30 => 'SS up to 30'] as $max => $label)
        <option value="{{ $max }}">{{ $label }}</option>
    @endforeach
</select>
<select data-filter="type" aria-label="Link type">
    <option value="">Link type</option>
    @foreach (['Guest post', 'Niche edit', 'Contextual', 'Text link'] as $type)
        <option value="{{ $type }}" @selected($defaultType === $type)>{{ $type }}</option>
    @endforeach
</select>
<select data-filter="validity" aria-label="Link validity">
    <option value="">Link validity</option>
    @foreach (['Permanent', '12 months', '6 months'] as $validity)
        <option value="{{ $validity }}">{{ $validity }}</option>
    @endforeach
</select>
<select data-filter="news" aria-label="Google News">
    <option value="">Google News</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
</select>
<select data-filter="sponsored" aria-label="Marked as sponsored">
    <option value="">Marked as sponsored</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
</select>
<select data-filter="country" aria-label="Country">
    <option value="">Country</option>
    @foreach ($countries as $country)
        <option value="{{ $country }}" @selected((string) $defaultCountry === (string) $country)>{{ $country }}</option>
    @endforeach
</select>
<select data-filter="foreign" aria-label="Foreign language allowed">
    <option value="">Foreign lang. allowed?</option>
    <option value="yes">Yes</option>
    <option value="no">No</option>
</select>
