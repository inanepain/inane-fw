/**
 * Throwable Wrapper
 *
 * Loading Throwable as a script
 */
(async () => {
    // Check for a global Throwable object
    if (window.Throwable == undefined) {
        // import module for side effects, global Throwable created
        await import(`./throwable.js`);
    }
})();
