(function () {
	'use strict';

	function parseList( value ) {
		return value
			? value.split( ',' ).map( function ( v ) { return v.trim(); } ).filter( Boolean )
			: [];
	}

	function phoneSelector() {
		var selector = '.wpcf7-intltel';
		if ( IntltelCf7.legacySelector ) {
			selector += ', ' + IntltelCf7.legacySelector;
		}
		return selector;
	}

	var geoCountryPromise = null;

	function detectCountryByIp() {
		if ( ! geoCountryPromise ) {
			geoCountryPromise = fetch( 'https://ipapi.co/json/' )
				.then( function ( res ) { return res.json(); } )
				.then( function ( data ) { return ( data && data.country_code ) ? data.country_code : IntltelCf7.defaultCountry; } )
				.catch( function () { return IntltelCf7.defaultCountry; } );
		}
		return geoCountryPromise;
	}

	// utils.js — это ~260 КБ метаданных для форматирования/проверки
	// номеров (в т.ч. плейсхолдер с примером номера). Не грузим его
	// сразу в момент инициализации поля (чтобы не мешать первому
	// рендеру страницы), но и не ждём именно клика по полю — иначе
	// плейсхолдер появляется только после фокуса. Планируем загрузку
	// на "простой" в браузере сразу после отрисовки страницы, а фокус
	// на поле форсирует её немедленно, если простоя ещё не было.
	var utilsRequested = false;

	function ensureUtilsLoaded() {
		if ( utilsRequested ) {
			return;
		}
		utilsRequested = true;

		window.intlTelInput.attachUtils( function () {
			return import( IntltelCf7.utilsUrl );
		} );
	}

	if ( window.requestIdleCallback ) {
		requestIdleCallback( ensureUtilsLoaded, { timeout: 2000 } );
	} else {
		setTimeout( ensureUtilsLoaded, 1000 );
	}

	document.addEventListener( 'focusin', function ( e ) {
		if ( e.target && e.target.classList && e.target.classList.contains( 'wpcf7-intltel' ) ) {
			ensureUtilsLoaded();
		}
	}, true );

	function initField( input ) {
		if ( input.dataset.itiInitialized ) {
			return;
		}

		input.classList.add( 'wpcf7-intltel' );

		var options = {
			// В самом поле должен остаться только национальный номер —
			// код страны хранится отдельно, в скрытом поле "-code"
			// (по умолчанию в v28 nationalMode:false, и в поле пишется
			// полный номер вида "+7 999...", что задваивает код в письме).
			nationalMode: true,
			i18n: {
				searchPlaceholder: IntltelCf7.searchPlaceholder,
			},
		};

		if ( input.dataset.initialCountry ) {
			options.initialCountry = input.dataset.initialCountry;
		} else if ( IntltelCf7.autoDetectCountry ) {
			options.initialCountry = 'auto';
			options.geoIpLookup = detectCountryByIp;
		} else {
			options.initialCountry = IntltelCf7.defaultCountry || 'ru';
		}

		var preferred = parseList( input.dataset.preferredCountries );
		if ( preferred.length ) {
			options.preferredCountries = preferred;
		}

		var only = parseList( input.dataset.onlyCountries );
		if ( only.length ) {
			options.onlyCountries = only;
		}

		var exclude = parseList( input.dataset.excludeCountries );
		if ( exclude.length ) {
			options.excludeCountries = exclude;
		}

		window.intlTelInput( input, options );
		input.dataset.itiInitialized = 'true';

		var computed = window.getComputedStyle( input );
		document.documentElement.style.setProperty( '--intltel-font-family', computed.fontFamily );
		document.documentElement.style.setProperty( '--intltel-font-size', computed.fontSize );
	}

	function initAllFields( root ) {
		try {
			root.querySelectorAll( phoneSelector() ).forEach( initField );
		} catch ( e ) {
			// Некорректный CSS-селектор в настройках "Классы полей телефона"
			// не должен ронять остальную обработку формы.
		}
	}

	function getCodeField( input ) {
		var wrap = input.closest( '.wpcf7-intltel-wrap' );
		if ( wrap ) {
			var wrapCodeField = wrap.querySelector( '.wpcf7-intltel-code' );
			if ( wrapCodeField ) {
				return wrapCodeField;
			}
		}

		if ( IntltelCf7.legacyCodeSelector ) {
			var form = input.closest( 'form' );
			if ( form ) {
				return form.querySelector( IntltelCf7.legacyCodeSelector );
			}
		}

		return null;
	}

	// Только заполняет скрытое поле кодом страны перед отправкой.
	// Саму валидацию (обязательность, корректность номера) полностью
	// делает стандартный механизм CF7 на сервере — единый AJAX-запрос
	// вместе с остальными полями формы, с обычным спиннером и тем же
	// способом показа ошибок (.wpcf7-not-valid-tip), что и у всех
	// остальных полей.
	function syncPhoneField( input ) {
		var iti = window.intlTelInput.getInstance( input );
		if ( ! iti ) {
			return;
		}

		var codeField = getCodeField( input );
		if ( codeField ) {
			var dialCode = iti.getSelectedCountryData().dialCode;
			codeField.value = dialCode ? '+' + dialCode : '';
		}
	}

	document.addEventListener( 'DOMContentLoaded', function () {
		initAllFields( document );
	} );

	new MutationObserver( function ( mutations ) {
		mutations.forEach( function ( mutation ) {
			mutation.addedNodes.forEach( function ( node ) {
				if ( node.nodeType !== 1 ) {
					return;
				}
				try {
					if ( node.matches && node.matches( phoneSelector() ) ) {
						initField( node );
						return;
					}
				} catch ( e ) {
					// см. комментарий в initAllFields()
				}
				if ( node.querySelectorAll ) {
					initAllFields( node );
				}
			} );
		} );
	} ).observe( document.body, { childList: true, subtree: true } );

	document.addEventListener( 'submit', function ( e ) {
		var form = e.target;
		if ( ! form.classList || ! form.classList.contains( 'wpcf7-form' ) ) {
			return;
		}

		form.querySelectorAll( '.wpcf7-intltel' ).forEach( syncPhoneField );
	}, true );
})();
