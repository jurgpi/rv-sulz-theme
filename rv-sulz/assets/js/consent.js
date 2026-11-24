/* Einwilligung für Statistik: Banner, Laden nach Erlaubnis, Widerruf (siehe inc/consent.php). */
( function () {
	'use strict';
	var KEY = 'rv-sulz-consent';
	var MAX_AGE = 365 * 24 * 60 * 60 * 1000; // nach 12 Monaten neu fragen
	var banner = document.getElementById( 'rv-consent' );
	var blocked = document.querySelectorAll( 'script[type="text/plain"][data-rv-consent]' );
	if ( !banner || !blocked.length ) { return; }

	function read() {
		try {
			var v = JSON.parse( localStorage.getItem( KEY ) || 'null' );
			if ( v && typeof v.statistik === 'boolean' && Date.now() - ( v.t || 0 ) < MAX_AGE ) { return v; }
		} catch ( e ) {}
		return null;
	}
	function write( ok ) {
		try { localStorage.setItem( KEY, JSON.stringify( { statistik: ok, t: Date.now(), v: 1 } ) ); } catch ( e ) {}
	}
	function activate() {
		for ( var i = 0; i < blocked.length; i++ ) {
			var old = blocked[ i ];
			if ( old.dataset.rvLoaded ) { continue; }
			var s = document.createElement( 'script' );
			for ( var a = 0; a < old.attributes.length; a++ ) {
				var at = old.attributes[ a ];
				if ( [ 'type', 'data-src', 'data-rv-consent' ].indexOf( at.name ) === -1 ) { s.setAttribute( at.name, at.value ); }
			}
			s.src = old.getAttribute( 'data-src' );
			s.async = true;
			old.dataset.rvLoaded = '1';
			old.parentNode.insertBefore( s, old.nextSibling );
		}
	}
	function clearCookies() {
		var host = location.hostname.split( '.' );
		document.cookie.split( ';' ).forEach( function ( c ) {
			var name = c.split( '=' )[ 0 ].trim();
			if ( !/^(_ga|_gid|_gat|_gcl)/.test( name ) ) { return; }
			for ( var i = 0; i < host.length - 1; i++ ) {
				var d = host.slice( i ).join( '.' );
				document.cookie = name + '=; Max-Age=0; path=/; domain=' + d;
				document.cookie = name + '=; Max-Age=0; path=/; domain=.' + d;
			}
			document.cookie = name + '=; Max-Age=0; path=/';
		} );
	}
	var lastFocus = null;
	function show() {
		lastFocus = document.activeElement;
		banner.hidden = false;
		var current = read();
		var btns = banner.querySelectorAll( '[data-rv-consent-choice]' );
		for ( var i = 0; i < btns.length; i++ ) {
			btns[ i ].setAttribute( 'aria-pressed', current && String( +current.statistik ) === btns[ i ].dataset.rvConsentChoice ? 'true' : 'false' );
		}
		requestAnimationFrame( function () { banner.classList.add( 'is-open' ); } );
	}
	function hide() {
		banner.classList.remove( 'is-open' );
		banner.hidden = true;
		if ( lastFocus && lastFocus.focus && document.contains( lastFocus ) ) { lastFocus.focus(); }
	}

	banner.addEventListener( 'click', function ( e ) {
		var b = e.target.closest( '[data-rv-consent-choice]' );
		if ( !b ) { return; }
		var ok = b.dataset.rvConsentChoice === '1';
		var before = read();
		write( ok );
		hide();
		if ( ok ) {
			activate();
		} else if ( before && before.statistik ) {
			// Widerruf: Statistik-Cookies löschen und ohne Google-Skript neu laden.
			clearCookies();
			location.reload();
		}
	} );
	document.addEventListener( 'click', function ( e ) {
		if ( e.target.closest && e.target.closest( '.rv-consent-open' ) ) { e.preventDefault(); show(); }
	} );

	var opens = document.querySelectorAll( '.rv-consent-open-wrap' );
	for ( var i = 0; i < opens.length; i++ ) { opens[ i ].hidden = false; }

	var choice = read();
	if ( choice && choice.statistik ) { activate(); }
	else if ( !choice ) { show(); }
} )();
