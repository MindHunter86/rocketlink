'use strict';

// const canWAAPI = typeof Element !== "undefined" && typeof Element.prototype.animate === "function";
function hasWAAPI(el) {
  return !!el && typeof el.animate === "function";
}

function switchSceneForForms(prevel, currel) {
  if (hasWAAPI(prevel) && hasWAAPI(currel)) {
    const elanim = prevel.animate(
      [{ opacity: 1, transform: "translateY(0)" }, { opacity: 0, transform: "translateY(8px)" }],
      { duration: 150, easing: "ease-in", fill: "forwards" }
    );
    elanim.onfinish = () => {
      prevel.classList.toggle('is-visible');
      currel.classList.toggle('is-visible');
      currel.animate(
        [{ opacity: 0, transform: 'translateY(8px)' }, { opacity: 1, transform: 'translateY(0)' }],
        { duration: 150, easing: 'ease-out', fill: 'forwards' }
      );
    };

    return;
  }

  // ? TODO: rework fallback method for old devices
  // TODO: test fallback method
  console.log('no WAAPI support!');

  prevel.classList.toggle('is-visible');
  currel.classList.toggle('is-visible');
  return;
}

function switchSceneForSections(prevel, currel) {
  if (typeof prevel === "undefined" || typeof currel === "undefined") return false;
  if (prevel === null || currel === null) return false;

  if (hasWAAPI(prevel) && hasWAAPI(currel)) {
    const elanim = prevel.animate(
      [{ opacity: 1 }, { opacity: 0 }],
      { duration: 150, easing: "ease-in", fill: "forwards" }
    );
    elanim.onfinish = () => {
      prevel.classList.toggle('is-visible');
      currel.classList.toggle('is-visible');
      currel.animate(
        [{ opacity: 0 }, { opacity: 1 }],
        { duration: 150, easing: 'ease-out', fill: 'forwards' }
      );
    };

    return true;
  }

  // ? TODO: rework fallback method for old devices
  // TODO: test fallback method
  console.log('no WAAPI support!');

  prevel.classList.toggle('is-visible');
  currel.classList.toggle('is-visible');
  return true;
}

document.addEventListener('DOMContentLoaded', function () {
  //
  //
  // Authorization
  const formFormCommon = this.getElementById('form_auth-common');
  const formFormSignIn = this.getElementById('form_auth-signin');
  const formFormSignUp = this.getElementById('form_auth-signup');
  const formFormSuccess = this.getElementById('form_auth-success');

  formFormCommon.addEventListener('submit', () => {
    switchSceneForForms(formFormCommon, formFormSignIn);
    // switchSceneForForms(formFormCommon, formFormSignUp);
    // switchElementOpacity(formFormCommon, true);
    // switchElementOpacity(formFormSignIn);

    // formFormCommon.classList.toggle('is-visible');
    // formFormSignUp.classList.toggle('is-visible');
    // formFormSignIn.classList.toggle('is-visible');
  });
  formFormSignIn.addEventListener('submit', () => {
    switchSceneForForms(formFormSignIn, formFormSuccess);

    window.alert('signin done');
    // formFormSignIn.classList.toggle('is-visible');
    // formFormCommon.classList.toggle('is-visible');

    // switchElementOpacity(formFormSignIn, true);
    // switchElementOpacity(formFormCommon);
  });
  formFormSignUp.addEventListener('submit', () => {
    switchSceneForForms(formFormSignUp, formFormSuccess);

    window.alert('signup done');
    // formFormSignUp.classList.toggle('is-visible');
    // formFormCommon.classList.toggle('is-visible');
  });

  //
  //
  // Payment
  const formPaymentUpdate = this.getElementById('form_payment-update');

  formPaymentUpdate.addEventListener('submit', () => {
    window.alert('updated');
  });

  //
  //
  // Main
  const shortenBtn = this.getElementById('test-shorten-button');
  const shortenForm = this.getElementById('shorten-form');


  shortenBtn.onclick = function () {
    shortenForm.classList.toggle('shorten');
    shortenForm.classList.toggle('shorten-cascade');
  };


  // legacy
  // const debugLoginPage = document.getElementById('debug_login-page');
  // const pageMainPage = document.getElementById('page_main-page');
  // const pageLoginPage = document.getElementById('page_login-page');

  const pageSectionMain = this.getElementById('page_section-main');
  const pageSectionPrices = this.getElementById('page_section-prices');
  const pageSectionAbout = this.getElementById('page_section-about');
  const pageSectionGithub = this.getElementById('page_section-github');
  const pageSectionLogin = this.getElementById('page_section-auth');

  let navCurrentPage = pageSectionMain;
  const navLinkHome = this.getElementById('nav_link-home');
  const navLinkPrices = this.getElementById('nav_link-prices');
  const navLinkAbout = this.getElementById('nav_link-about');
  const navLinkGithub = this.getElementById('nav_link-github');
  const navLinkLogin = this.getElementById('nav_link-login');

  navLinkHome.onclick = function () {
    if (navCurrentPage === pageSectionMain) return;
    if (!switchSceneForSections(navCurrentPage, pageSectionMain)) {
      return;
    }
    navCurrentPage = pageSectionMain;
  }
  navLinkPrices.onclick = function () {
    if (navCurrentPage === pageSectionPrices) return;
    if (!switchSceneForSections(navCurrentPage, pageSectionPrices)) {
      return;
    }
    navCurrentPage = pageSectionPrices;
  }
  navLinkAbout.onclick = function () {
    if (navCurrentPage === pageSectionAbout) return;
    if (!switchSceneForSections(navCurrentPage, pageSectionAbout)) {
      return;
    }
    navCurrentPage = pageSectionAbout;
  }
  navLinkGithub.onclick = function () {
    if (navCurrentPage === pageSectionGithub) return;
    if (!switchSceneForSections(navCurrentPage, pageSectionGithub)) {
      return;
    }
    navCurrentPage = pageSectionGithub;
  }
  navLinkLogin.onclick = function () {
    if (navCurrentPage === pageSectionLogin) return;
    if (!switchSceneForSections(navCurrentPage, pageSectionLogin)) {
      return;
    }
    navCurrentPage = pageSectionLogin;
  }

  // !!! TO DELETE THIS CODE-BLOCK
  // DEBUG PAGES TESTCALLS
  const pageUSectionCart = this.getElementById('page_usection-cart');
  this.getElementById('nav_ulink-cart').onclick = function () {
    if (navCurrentPage === pageUSectionCart) return;
    if (!switchSceneForSections(navCurrentPage, pageUSectionCart)) {
      return;
    }
    navCurrentPage = pageUSectionCart;
  }

  const pageUSectionPayment = this.getElementById('page_usection-addpayment');
  this.getElementById('nav_ulink-payment').onclick = function () {
    if (navCurrentPage === pageUSectionPayment) return;
    if (!switchSceneForSections(navCurrentPage, pageUSectionPayment)) {
      return;
    }
    navCurrentPage = pageUSectionPayment;
  }

  const pageUSectionShrtList = this.getElementById('page_usection-shrtlist');
  this.getElementById('nav_ulink-shrtlist').onclick = function () {
    if (navCurrentPage === pageUSectionShrtList) return;
    if (!switchSceneForSections(navCurrentPage, pageUSectionShrtList)) {
      return;
    }
    navCurrentPage = pageUSectionShrtList;
  }

  const pageUSectionShrtDtls = this.getElementById('page_usection-shrtdtls');
  this.getElementById('nav_ulink-shrtdtls').onclick = function () {
    if (navCurrentPage === pageUSectionShrtDtls) return;
    if (!switchSceneForSections(navCurrentPage, pageUSectionShrtDtls)) {
      return;
    }
    navCurrentPage = pageUSectionShrtDtls;
  }
})