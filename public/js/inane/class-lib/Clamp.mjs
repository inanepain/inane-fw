/**
 * A utility class for clamping values within a specified range.
 */
class Clamp {
    /**
     * Restricts a given value to be within a specified range defined by a minimum and maximum.
     *
     * @param {number} value - The value to clamp.
     * @param {number} min - The lower bound of the range.
     * @param {number} max - The upper bound of the range.
     * @return {number} The clamped value, constrained between the min and max bounds.
     */
    clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }
}

export { Clamp}
