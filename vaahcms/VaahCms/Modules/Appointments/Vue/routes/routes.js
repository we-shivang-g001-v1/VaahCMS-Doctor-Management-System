let routes= [];

import dashboard from "./vue-routes-dashboard";
import doctor from "./vue-routes-doctors";
import patient from "./vue-routes-patients";

routes = routes.concat(dashboard);
routes = routes.concat(doctor);
routes = routes.concat(patient);

export default routes;
