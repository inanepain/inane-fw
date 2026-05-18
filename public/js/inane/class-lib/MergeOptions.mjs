/**
 * A utility class for defining and controlling object merge operations.
 * Provides options for adding, updating, or both adding and updating properties
 * during object merging.
 */
class MergeOptions {
    /**
     * Only update existing keys on the target.
     */
    static get mergeMethodUpdateOnly() {
        return 'Update';
    }

    /**
     * Only add new keys to the target.
     */
    static get mergeMethodAddOnly() {
        return 'Add';
    }

    /**
     * Update existing keys and add new keys to the target.
     */
    static get mergeMethodAddAndUpdate() {
        return MergeOptions.mergeMethodUpdateOnly + MergeOptions.mergeMethodAddOnly;
    }

    /**
     * Sets how and what is merged.
     */
    #mergeMethod = MergeOptions.mergeMethodUpdateOnly;

    /**
     * Retrieves the current merge method used by the instance.
     *
     * @return {string} The merge method configured for this instance.
     */
    get mergeMethod() {
        return this.#mergeMethod;
    }

    /**
     * Sets the merge method for the current instance.
     *
     * @param {string} value - The merge method to set. Must be one of the following:
     * MergeOptions.mergeMethodAddOnly, MergeOptions.mergeMethodUpdateOnly, or MergeOptions.mergeMethodAddAndUpdate.
     */
    set mergeMethod(value) {
        if ([MergeOptions.mergeMethodAddOnly, MergeOptions.mergeMethodUpdateOnly, MergeOptions.mergeMethodAddAndUpdate].includes(value)) {
            this.#mergeMethod = value;
        }
    }

    /**
     * Merges properties from source objects into the target object using the specified merge method.
     *
     * @param {string} mergeMethod - The method to use for merging (e.g., add-only or update-only).
     * @param {Object} target - The target object to which properties will be merged.
     * @param {...Object} sources - One or more source objects whose properties will be merged into the target.
     *
     * @return {Object} The merged target object after applying the specified merge method.
     */
    static mergeOptionsWithMethod(mergeMethod, target, ...sources) {
        for (const source of sources) {
            for (const key in source) {
                if ((mergeMethod === MergeOptions.mergeMethodAddOnly && Object.prototype.hasOwnProperty.call(target, key)) || (mergeMethod === MergeOptions.mergeMethodUpdateOnly && !Object.prototype.hasOwnProperty.call(target, key))) {
                    continue;
                }

                const sourceValue = source[key];
                const targetValue = target[key];
                const isObject =
                    sourceValue &&
                    targetValue &&
                    typeof sourceValue === 'object' &&
                    typeof targetValue === 'object' &&
                    !Array.isArray(sourceValue) &&
                    !Array.isArray(targetValue);

                if (isObject) {
                    MergeOptions.mergeOptionsWithMethod(mergeMethod, targetValue, sourceValue);
                } else {
                    target[key] = sourceValue;
                }
            }
        }

        return target;
    }

    /**
     * Merges multiple source objects into the target object using the "add-only" merge strategy.
     * This method only adds properties that do not already exist on the target object.
     *
     * @param {Object} target The target object to which properties will be added.
     * @param {...Object} sources One or more source objects containing properties to be merged into the target.
     *
     * @return {Object} The updated target object after merging with "add-only" behavior.
     */
    static mergeOptionsWithAddOnly(target, ...sources) {
        return MergeOptions.mergeOptionsWithMethod(MergeOptions.mergeMethodAddOnly, target, ...sources);
    }

    /**
     * Merges the given sources into the target object using the "update-only" merge method.
     * The "update-only" method ensures that only existing properties in the target object
     * are updated, without adding new properties.
     *
     * @param {Object} target - The target object to which properties from the sources will be merged.
     * @param {...Object} sources - One or more source objects containing properties to merge into the target.
     *
     * @return {Object} The target object with updated properties from the sources.
     */
    static mergeOptionsWithUpdateOnly(target, ...sources) {
        return MergeOptions.mergeOptionsWithMethod(MergeOptions.mergeMethodUpdateOnly, target, ...sources);
    }

    /**
     * Merges the given source objects into the target object using the "Add and Update" merge method.
     *
     * @param {Object} target - The target object that will be merged into.
     * @param {...Object} sources - One or more source objects to merge into the target object.
     *
     * @return {Object} The target object after merging the specified sources.
     */
    static mergeOptionsWithAddAndUpdate(target, ...sources) {
        return MergeOptions.mergeOptionsWithMethod(MergeOptions.mergeMethodAddAndUpdate, target, ...sources);
    }

    /**
     * Adds ONLY missing properties from source objects to the target in decreasing priority
     *
     * @example
     * // copy values from defaults missing in options to options
     * I.mergeOptions(options, defaults);
     *
     * @param {Object} target the target object
     * @param {Object|Object[]} sources the source objects in decreasing order of priority
     *
     * @returns {Object} target with missing properties from objs
     */
    mergeOptions(target, ...sources) {
        return MergeOptions.mergeOptionsWithMethod(this.mergeMethod, target, ...sources);
    }
}

export {MergeOptions};
