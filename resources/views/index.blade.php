@extends('layouts.staticPages', ['page' => 'startpage'])

@section('title', $title )

@section('content')
	<h1 id="startpage-logo">
		<a href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/") }}">
			<img src="/img/metager.svg" alt="MetaGer" />
		</a>
	</h1>
	@include('parts.searchbar', ['class' => 'startpage-searchbar'])
	<div id="plugin-btn-div">
		<a id="plugin-btn" href="{{ LaravelLocalization::getLocalizedURL(LaravelLocalization::getCurrentLocale(), "/plugin") }}" title="{{ trans('index.plugin-title') }}"><i class="fa fa-plug" aria-hidden="true"></i> {{ trans('index.plugin') }}</a>
	</div>
	<div id="plugin-btn-div">
    <a id="plugin-btn" href="https://metager.de/plugin" title="MetaGer zu Ihrem Browser hinzufügen"><img src="/img/plug-in.svg" alt="Plus-Zeichen"></i> MetaGer-Plugin hinzufügen</a>
	</div>
	<a id="scroll-helper" href="#about-us">
		<i class="fas fa-angle-double-down"></i>
	</a>
    <div id="center-scroll-link">
       <div id="scroll-link">
    <a href="#story-privacy" class="four-reasons">4 Gründe MetaGer zu nutzen</a>
    <a href="#story-privacy" title="Garantierte Privatsphäre"><img src="/img/lock.svg" alt="Sicherheitsschloss"></a>
    <a href="#story-ngo" title="Gemeinnütziger Verein"><img src="/img/heart.svg" alt="Herz"></a>
    <a href="#story-diversity" title="Vielfältig & Frei"><img src="/img/rainbow.svg" alt="Regenbogen"></a>
    <a href="#story-eco"title="100% Ökostrom"><img src="/img/leaf.svg" alt="grünes Blatt"></a>
  </div>
    </div>
   	<footer class="startPageFooter noprint">
  <div>
    <a href="https://metager.de/kontakt">Kontakt</a>
    <a href="https://metager.de/impressum">Impressum</a>
    <a href="https://metager.de/datenschutz">Datenschutz</a>
  </div>
  </footer>
    <div id="story-container">
      <section id="story-privacy">
        <h1>Garantierte Privatsphäre</h1>
        <ul class="story-links"> 
       <li><a class="story-button" href="https://metager.de/about">Über uns</a></li>
       <li><a class="story-button" href="https://metager.de/datenschutz">Unsere Datenschutzerklärung</a></li>
        </ul>
        <figure class="story-icon">
          <img src="/img/lock.svg" alt="Sicherheitsschloss">
        </figure>
        <p>Mit uns behalten Sie die volle Kontrolle über Ihre Daten. Mit der Anonym-Öffnen-Funktion bleiben Sie auch beim Weitersurfen geschützt. <br> Wir tracken nicht. Wir speichern nicht.</p>
      </section>
      <section id="story-ngo">
        <h1>Gemeinnütziger Verein</h1>

       <ul class="story-links">
        <li><a class="story-button" href="https://suma-ev.de/"> Der SUMA-EV</a></li>
        <li><a class="story-button" href="https://metager.de/spende">Spendenformular</a></li>
        <li><a class="story-button" href="https://metager.de/beitritt">Mitgliedsantrag</a></li>
        <li><a class="story-button" href="https://suma-ev.de/mitglieder/"> Weitere Mitgliedervorteile</a></li>       </ul>
        <figure class="story-icon">
        <img src="/img/heart.svg" alt="Herz">
        </figure>
        <p>Metager wird getragen vom gemeinnützigen SUMA-EV, Verein für freien Wissenszugang. Unterstützen Sie uns, indem Sie spenden oder Mitglied werden. Mitglieder suchen auf Metager werbefrei.</p>
      </section>
      <section id="story-diversity">
        <h1>Vielfältig &amp; Frei</h1>
        <ul class="story-links">
        <li><a class="story-button" href="https://metager.de/about"> Über uns</a></li>
        <li><a class="story-button" href="https://gitlab.metager.de/open-source/MetaGer"> Metager-Quellcode</a></li>
        <li><a class="story-button" href="https://metager.de/about"> Unser Algorithmus</a></li>
        </ul>
        <figure class="story-icon">
          <img src="/img/rainbow.svg" alt="Regenbogen">
        </figure>
        <p>Metager schützt gegen Zensur, indem es Ergebnisse vieler Suchmaschinen kombiert. Unsere Algorithmen sind transparent und für jeden einsehbar.<br>Der Quellcode ist frei und open-source.</p>
      </section>
  
      <section id="story-eco">
        <h1>100% Ökostrom</h1>
        <ul class="story-links">
        <li><a class="story-button" href="https://www.hetzner.de/unternehmen/umweltschutz/"> Mehr dazu</a></li>
        </ul>
        <figure class="story-icon">
          <img src="/img/leaf.svg" alt="grünes Blatt">
        </figure>
        <p>Wir achten auf die Nachhaltigkeit und den Resourcenverbrauch unserer Dienste. Wir verwenden nur Strom aus regenerativen Energiequellen.<br>Vom Server bis zur Kaffeemaschine.</p>
      </section>
      <section id="story-plugin">
        <h1>Jetzt MetaGer installieren</h1>
        <ul class="story-links">
        <li><a class="story-button" href="https://metager.de/plugin"> MetaGer-Plugin hinzufügen</a></li>
        <li><a class="story-button" href="https://metager.de/app"> MetaGer-App</a></li>
        </ul>
        <figure class="story-icon">
          <picture>
            <source media="(max-width: 760px)" srcset="/img/App.svg">
                    <img src="/img/story-plugin.svg" alt="Metager-Apps">  
          </picture>

        </figure>
        <p>Mit unserem Plugin können Sie MetaGer als Standardsuchmaschine festlegen. Und mit der App nutzen Sie MetaGer ganz bequem auf Ihrem Smartphone.</p>
      </section>
    </div> 
@endsection
