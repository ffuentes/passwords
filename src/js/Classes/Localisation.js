export default class Localisation {

    /**
     *
     * @param text
     * @param variables
     * @returns {string}
     */
    static translate(text, variables = {}) {
        if(text === undefined) return '';
        if(OC !== undefined) return OC.L10N.translate('passwords', text, variables);

        return '';
    }

    /**
     *
     * @param date
     * @returns {string}
     */
    static formatDate(date) {
        return OC.Util.relativeModifiedDate(date);
    }

    /**
     *
     * @param date
     * @returns {string}
     */
    static formatDateTime(date) {
        return OC.Util.formatDate(date, 'LLL');
    }
}