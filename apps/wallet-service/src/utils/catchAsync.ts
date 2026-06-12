import { RequestHandler, Router } from 'express';
import { Request, Response, NextFunction } from 'express-serve-static-core';

export interface CustomParamsDictionary {
  [key: string]: any;
}

const catchAsync =
  (fn: RequestHandler<CustomParamsDictionary, any, any, qs.ParsedQs, Record<string, any>>) =>
  (
    req: Request<CustomParamsDictionary, any, any, any, Record<string, any>>,
    res: Response<any, Record<string, any>, number>,
    next: NextFunction
  ) => {
    Promise.resolve(fn(req, res, next)).catch((err) => next(err));
  };

// Utility to wrap all routes in asyncHandler
export const wrapRoutes = (router: Router) => {
  router.stack.forEach((layer) => {
    if (layer.route) {
      // If it's a route, wrap each of the route's methods
      layer.route.stack.forEach((routeHandler, index) => {
        layer.route.stack[index].handle = catchAsync(routeHandler.handle);
      });
    } else if (layer.name === 'router' && layer.handle.stack) {
      // If it's a nested router, call wrapRoutes recursively
      wrapRoutes(layer.handle);
    }
  });
};

export default catchAsync;
