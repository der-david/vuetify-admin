const { chalk } = require(require.resolve("@vue/cli-shared-utils"));
const fs = require("fs");
const yaml = require("js-yaml");
const make = require("./make");
const isEmpty = require("lodash/isEmpty");

const options = {
  description: "resource ui crud generator",
  usage: "vue-cli-service crud:yaml [options]",
  options: {
    file: "Yaml file descriptor.",
    name: "Optional name of model to import, if not set, all will be imported.",
    output: "Output directory of resource generated crud pages.",
  },
};

async function service(args = {}, api) {
  if (!args.file) {
    console.log(chalk.red(`not specified descriptor 'file' argument.`));
    return;
  }

  /**
   * Generate crud views
   */
  const descriptor = yaml.safeLoad(fs.readFileSync(args.file, "utf8"));

  Object.keys(descriptor.resources)
    .filter((n) => (args.name ? args.name === n : true))
    .forEach((name) => {
      let resource = descriptor.resources[name];

      const fields = Object.keys(resource.fields)
        .map((name) => {
          return {
            name,
            ...resource.fields[name],
          };
        })
        .map((field) => {
          return {
            ...field,
            type: field.type || "text",
          };
        });

      make.service({
        output: args.output,
        name,
        label: resource.label,
        icon: resource.icon,
        actions: resource.actions,
        translatable: !!resource.translatable,
        columns: resource.columns,
        include: resource.include,
        filterable: resource.filterable,
        sortable: resource.sortable,
        searchable: !isEmpty(resource.searchable),
        locale: args.locale || "en",
        fields,
      });
    });
}

module.exports = {
  service,
  options,
};
