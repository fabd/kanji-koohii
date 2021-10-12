/**
 * On-demand import of element-plus components.
 *
 * See https://element-plus.org/#/en-US/component/quickstart
 *
 *   - Import only the components we need in our app
 *   - Import only the stylesheets for those components
 *
 * Then we re-export, this simplifies importing in other templates:
 *
 *   import { ElButton, ElAside, ... } from "@/lib/vendor/element";
 *
 * Instead of two lines per component:
 *
 *   import { ElButton } from "element-plus";
 *   import "element-plus/es/components/button/style/css";
 *   import { Aside } from "element-plus";
 *   import "element-plus/es/components/aside/style/css";
 *   ...
 *
 * Notes
 *   - full import 'element-plus/dist/index.css' is 300+ kb, which is
 *     a bit much, and unnecessary since we won't use many of those
 *     components.
 *
 */
/* eslint-disable @typescript-eslint/no-unused-vars */

import { ElButton } from "element-plus";
import "element-plus/es/components/button/style/css";

export { ElButton };
