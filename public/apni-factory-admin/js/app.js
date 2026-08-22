$(function () {
	"use strict";
	/* perfect scrol bar */
	new PerfectScrollbar('.header-message-list');
	new PerfectScrollbar('.header-notifications-list');
	// search bar
	$(".mobile-search-icon").on("click", function () {
		$(".search-bar").addClass("full-search-bar");
		$(".page-wrapper").addClass("search-overlay");
	});
	$(".search-close").on("click", function () {
		$(".search-bar").removeClass("full-search-bar");
		$(".page-wrapper").removeClass("search-overlay");
	});
	$(".mobile-toggle-menu").on("click", function () {
		$(".wrapper").addClass("toggled");
	});
	// toggle menu button
	$(".toggle-icon").click(function () {
		if ($(".wrapper").hasClass("toggled")) {
			// unpin sidebar when hovered
			$(".wrapper").removeClass("toggled");
			$(".sidebar-wrapper").unbind("hover");
		} else {
			$(".wrapper").addClass("toggled");
			$(".sidebar-wrapper").hover(function () {
				$(".wrapper").addClass("sidebar-hovered");
			}, function () {
				$(".wrapper").removeClass("sidebar-hovered");
			})
		}
	});
	// dark mode
	$(".dark-mode").on("click", function() {

		if($(".dark-mode-icon i").attr("class") == 'bx bx-sun') {
			$(".dark-mode-icon i").attr("class", "bx bx-moon");
			$("html").attr("class", "light-theme")
		} else {
			$(".dark-mode-icon i").attr("class", "bx bx-sun");
			$("html").attr("class", "dark-theme")
		}

	}), 
	/* Back To Top */
	$(document).ready(function () {
		$(window).on("scroll", function () {
			if ($(this).scrollTop() > 300) {
				$('.back-to-top').fadeIn();
			} else {
				$('.back-to-top').fadeOut();
			}
		});
		$('.back-to-top').on("click", function () {
			$("html, body").animate({
				scrollTop: 0
			}, 600);
			return false;
		});
	});
	// === sidebar menu activation js
	$(function () {
		for (var i = window.location, o = $(".metismenu li a").filter(function () {
			return this.href == i;
		}).addClass("").parent().addClass("mm-active");;) {
			if (!o.is("li")) break;
			o = o.parent("").addClass("mm-show").parent("").addClass("mm-active");
		}
	});
	// metismenu
	$(function () {
		$('#menu').metisMenu();
	});
	// chat toggle
	$(".chat-toggle-btn").on("click", function () {
		$(".chat-wrapper").toggleClass("chat-toggled");
	});
	$(".chat-toggle-btn-mobile").on("click", function () {
		$(".chat-wrapper").removeClass("chat-toggled");
	});
	// email toggle
	$(".email-toggle-btn").on("click", function () {
		$(".email-wrapper").toggleClass("email-toggled");
	});
	$(".email-toggle-btn-mobile").on("click", function () {
		$(".email-wrapper").removeClass("email-toggled");
	});
	// compose mail
	$(".compose-mail-btn").on("click", function () {
		$(".compose-mail-popup").show();
	});
	$(".compose-mail-close").on("click", function () {
		$(".compose-mail-popup").hide();
	});

	
	

	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
});