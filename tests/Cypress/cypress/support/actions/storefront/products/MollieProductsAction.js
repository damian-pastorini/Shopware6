export default class MollieProductsAction {


    openListingRegularProducts() {
        this._visitDifferentPage();
        cy.visit('/Mollie/Regular-Products/');
    }

    openSubscriptionProduct_Weekly3() {
        this._visitDifferentPage();
        cy.visit('/Subscription-3x-Weekly/MOL-SUB-3');
    }

    openRegularProduct() {
        this._visitDifferentPage();
        cy.visit('/Regular-Mollie-Shirt/MOL-CHEAP-2');
    }

    openEcoProduct() {
        this._visitDifferentPage();
        cy.visit('/Voucher-ECO/MOL-VOUCHER-1');
    }

    openMealProduct() {
        this._visitDifferentPage();
        cy.visit('/Voucher-MEAL/MOL-VOUCHER-2');
    }

    openGiftProduct() {
        this._visitDifferentPage();
        cy.visit('/Voucher-GIFT/MOL-VOUCHER-3');
    }


    /**
     * This is necessary, because Cypress caches our previously visited page
     * If we use a different URL in between, then it works
     * @private
     */
    _visitDifferentPage() {
        // we use a page with low performance impact
        cy.visit('/account/login');
    }
}