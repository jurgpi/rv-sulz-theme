/*
 * Archiv: Filter, Suche und „Mehr laden“ ohne Neuladen der Seite.
 * Holt die Seite mit den neuen Filterwerten und tauscht nur das Archiv aus.
 * Ohne JavaScript funktionieren alle Links und das Suchformular ganz normal.
 */
( function () {
	'use strict';

	var busy = false;
	var timer = null;

	function root() { return document.querySelector( '.rv-archiv' ); }

	function upgradeMore( scope ) {
		var more = ( scope || document ).querySelector( '.rv-archiv__more' );
		if ( more && more.dataset.labelJs ) {
			more.textContent = more.dataset.labelJs;
			more.classList.add( 'is-js' );
		}
		var prev = ( scope || document ).querySelector( '.rv-archiv__prev' );
		var pages = ( scope || document ).querySelector( '.rv-archiv__seiten' );
		// Mit „Mehr laden“ braucht es keine Seitenzahlen.
		if ( pages && !prev ) { pages.hidden = true; }
	}

	function load( url, opts ) {
		opts = opts || {};
		var el = root();
		if ( !el || busy ) { return; }
		busy = true;
		el.classList.add( 'is-loading' );
		el.setAttribute( 'aria-busy', 'true' );

		var focused = !opts.pop && document.activeElement && document.activeElement.id === 'rv-archiv-suche';
		var caret = focused ? document.activeElement.selectionStart : null;
		var typed = focused ? document.activeElement.value : null;

		fetch( url, { credentials: 'same-origin', headers: { 'X-Requested-With': 'rv-archiv' } } )
			.then( function ( r ) { if ( !r.ok ) { throw new Error( r.status ); } return r.text(); } )
			.then( function ( html ) {
				var doc = new DOMParser().parseFromString( html, 'text/html' );
				var neu = doc.querySelector( '.rv-archiv' );
				if ( !neu ) { throw new Error( 'kein Archiv' ); }

				if ( opts.append ) {
					var grid = el.querySelector( '.rv-archiv__grid' );
					var cards = neu.querySelectorAll( '.rv-archiv__grid > *' );
					var first = null;
					for ( var i = 0; i < cards.length; i++ ) {
						var n = document.importNode( cards[ i ], true );
						n.classList.add( 'is-new' );
						grid.appendChild( n );
						if ( !first ) { first = n; }
					}
					var oldPager = el.querySelector( '.rv-archiv__pager' );
					var newPager = neu.querySelector( '.rv-archiv__pager' );
					if ( oldPager ) {
						if ( newPager && newPager.querySelector( '.rv-archiv__more' ) ) {
							var p = document.importNode( newPager, true );
							var prev = p.querySelector( '.rv-archiv__prev' );
							if ( prev ) { prev.remove(); }
							var s = p.querySelector( '.rv-archiv__seiten' );
							if ( s ) { s.hidden = true; }
							oldPager.replaceWith( p );
							upgradeMore( p );
						} else {
							oldPager.remove();
						}
					}
					if ( first ) {
						var a = first.querySelector( 'a' );
						if ( a ) { a.focus( { preventScroll: true } ); }
					}
				} else {
					var fresh = document.importNode( neu, true );
					el.replaceWith( fresh );
					el = fresh;
					upgradeMore( fresh );
					if ( focused ) {
						var input = document.getElementById( 'rv-archiv-suche' );
						if ( input ) {
							input.focus();
							if ( typed !== null && input.value !== typed ) {
								input.value = typed; // während des Ladens weitergetippt
								setTimeout( function () { input.dispatchEvent( new Event( 'input', { bubbles: true } ) ); }, 0 );
							}
							try { input.setSelectionRange( caret, caret ); } catch ( e ) {}
						}
					}
					var top = fresh.querySelector( '.rv-archiv__status' ).getBoundingClientRect().top;
					if ( !focused && ( top < 0 || top > window.innerHeight * 0.6 ) ) {
						fresh.querySelector( '.rv-archiv__sparten, .rv-archiv__jahre' ).scrollIntoView( { behavior: 'smooth', block: 'start' } );
					}
					var clean = url.replace( /#.*$/, '' );
					if ( opts.replace ) { history.replaceState( { rvArchiv: 1 }, '', clean ); }
					else if ( !opts.pop ) { history.pushState( { rvArchiv: 1 }, '', clean ); }
					// Aktive Jahres-Kachel sichtbar machen (Handy: waagrecht scrollbar).
					var act = fresh.querySelector( '.rv-jahr.is-active' );
					var bar = fresh.querySelector( '.rv-archiv__jahre' );
					if ( act && bar && bar.scrollWidth > bar.clientWidth ) {
						bar.scrollLeft = act.offsetLeft - bar.clientWidth / 2 + act.clientWidth / 2;
					}
				}
			} )
			.catch( function () { window.location.href = url; } )
			.then( function () {
				busy = false;
				var r = root();
				if ( r ) { r.classList.remove( 'is-loading' ); r.removeAttribute( 'aria-busy' ); }
			} );
	}

	function formUrl( form ) {
		var url = new URL( form.action, window.location.href );
		url.hash = '';
		var data = new FormData( form );
		data.forEach( function ( v, k ) {
			v = String( v ).trim();
			if ( v ) { url.searchParams.set( k, v ); } else { url.searchParams.delete( k ); }
		} );
		url.searchParams.delete( 'seite' );
		return url.toString();
	}

	document.addEventListener( 'click', function ( e ) {
		var a = e.target.closest && e.target.closest( '.rv-archiv a[data-rv-nav]' );
		if ( !a || e.defaultPrevented || e.button !== 0 || e.ctrlKey || e.metaKey || e.shiftKey || e.altKey ) { return; }
		e.preventDefault();
		load( a.href, { append: a.hasAttribute( 'data-rv-more' ) } );
	} );

	document.addEventListener( 'submit', function ( e ) {
		var f = e.target.closest && e.target.closest( '.rv-archiv__suche' );
		if ( !f ) { return; }
		e.preventDefault();
		clearTimeout( timer );
		load( formUrl( f ) );
	} );

	// Suche während der Eingabe (kurz warten, ab 3 Zeichen oder bei leerem Feld).
	document.addEventListener( 'input', function ( e ) {
		if ( !e.target || e.target.id !== 'rv-archiv-suche' ) { return; }
		var f = e.target.form;
		var v = e.target.value.trim();
		clearTimeout( timer );
		if ( v.length > 0 && v.length < 3 ) { return; }
		timer = setTimeout( function () { load( formUrl( f ), { replace: true } ); }, 450 );
	} );

	window.addEventListener( 'popstate', function () {
		if ( root() ) { load( window.location.href, { pop: true } ); }
	} );

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function () { upgradeMore(); } );
	} else {
		upgradeMore();
	}
} )();
