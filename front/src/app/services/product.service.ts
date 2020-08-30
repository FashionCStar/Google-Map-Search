import { Injectable } from '@angular/core';
import {HttpClient} from '@angular/common/http';
import { map } from 'rxjs/operators';
import {Observable} from 'rxjs';

@Injectable({
  providedIn: 'root'
})
export class ProductService {
  // baseUrl = 'http://127.0.0.1:8000';
  baseUrl = 'https://map.spokane-paint.com';
  constructor(private _http: HttpClient) { }

  getPropertyList(polygon?: any[]): Observable<any> {
    if (polygon && polygon.length > 2)
      polygon.push(polygon[0]);
    const arrStr = (JSON.stringify(polygon));
    return this._http.get<any>(`${this.baseUrl}/api/property${polygon && polygon.length > 2 ? '?polygon=' + arrStr : ''}`, {
      headers: {
        'Content-Type': 'application/json'
      }});
  }
}
