<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ItemModel;
use Exception;

class Item extends ResourceController
{
  private $primaryKey = 'id';

  function __construct()
  {
    $this->model = new ItemModel();
  }

  // GET -> /item
  public function index()
  {
    $status = $this->request->getVar('status') ?? 'all';
    if (!in_array($status, ['to-do', 'done', 'hidden'])) $status = 'all';
    if ($status != 'all') $this->model->where('status', $status);
    $this->model->orderBy('id', 'DESC');
    $data = $this->model->findAll();

    if (count($data) > 0) {
      return $this->respond([
        'message' => 'Get List of Item Success',
        'result' => $data,
      ], 200, 'OK');
    }else {
      return $this->respond([
        'message' => 'No Item could be found',
        'result' => [],
      ], 404, 'Not Found');
    }
  }

  // PUT -> /item
  public function create()
  {
    $rules = [
      'detail' => 'required',
    ];
  
    $messages = [
      'detail' => [
        'required' => 'Detail of to-do item is required'
      ]
    ];

    if ($this->validate($rules, $messages)) {
      try {
        $input = $this->request->getRawInput() ?? $this->request->getJSON();
        $data = [
          'detail' => $input['detail'],
        ];
        if ($this->model->insert($data)) {
          return $this->respond([
            'message' => 'Item has been added to to-do list',
            'new_id' => $this->model->getInsertID()
          ], 201, 'Created');
        } else {
          return $this->respond([
            'message' => 'Something went wrong while inserting item, please try again later',
          ], 500, 'Internal Server Error');
        }
      } catch (Exception $ex) {
        return $this->respond([
          'message' => 'Something went wrong while inserting item, please try again later',
        ], 500, 'Internal Server Error');
      }
    } else {
      return $this->respond([
        'message' => 'Parameter(s) might not fulfilled basic requirement(s)',
        'parameter' => $this->validator->getErrors(),
      ], 400, 'Bad Request');
    }
  }

  // PUT -? /item/{id}
  public function update($id = null)
  {
    if (is_null($id)) {
      return $this->respond([
        'message' => 'To action an update, proper id should be sent as part of endpoint',
        'id' => null
      ], 400, 'Bad Request');
    } else {
      $input = $this->request->getRawInput() ?? $this->request->getJSON();
      switch ($this->request->getMethod()) {
        case 'put':
          if (isset($input['detail']) && isset($input['status'])) {
            $rules = [
              'detail' => 'required',
              'status' => 'required|in_list[to-do,done,hidden]',
            ];
            $messages = [
              'detail' => [
                'required' => 'Updated detail item is required'
              ],
              'status' => [
                'required' => 'Updated status item is required',
                'in_list' => "Updated status item must be filled between 'to-do', 'done', or 'hidden'",
              ]
            ];
            if ($this->validate($rules, $messages)) {
              try {
                if (count($this->model->where($this->primaryKey, $id)->findAll()) > 0) {
                  $this->model->set('detail', $input['detail']);
                  $this->model->set('status', $input['status']);
                  $this->model->where($this->primaryKey, $id);
                  if ($this->model->update()) {
                    return $this->respond([
                      'message' => 'Item has been updated',
                    ], 200, 'OK');
                  } else {
                    return $this->respond([
                      'message' => 'Something went wrong while updating item, please try again later',
                    ], 500, 'Internal Server Error');
                  }
                } else {
                  return $this->respond([
                    'message' => 'Item with this id is not found',
                  ], 404, 'Not Found');
                }
              } catch (Exception $ex) {
                return $this->respond([
                  'message' => 'Something went wrong while updating item, please try again later',
                ], 500, 'Internal Server Error');
              }
            } else {
              return $this->respond([
                'message' => 'Parameter(s) might not fulfilled basic requirement(s)',
                'parameter' => $this->validator->getErrors(),
              ], 400, 'Bad Request');
            }
          } else {
            return $this->respond([
              'message' => 'Both detail and status must be sent as body request to action updating item',
              'advice' => 'If you are going to update either just detail or status, maybe you should try PATCH method',
            ], 400, 'Bad Request');
          }
          break;
        case 'patch':
          if (isset($input['detail']) || isset($input['status'])) {
            if (isset($input['detail'])) {
              $rules['detail'] = 'required';
              $messages['detail']['required'] = 'Updated detail item is required';
            }
            if (isset($input['status'])) {
              $rules['status'] = 'required|in_list[to-do,done,hidden]';
              $messages['status']['required'] = 'Updated status item is required';
              $messages['status']['in_list'] = "Updated status item must be filled between 'to-do', 'done', or 'hidden'";
            }
            if ($this->validate($rules, $messages)) {
              try {
                if (count($this->model->where($this->primaryKey, $id)->findAll()) > 0) {
                  if (isset($input['detail'])) $this->model->set('detail', $input['detail']);
                  if (isset($input['status'])) $this->model->set('status', $input['status']);
                  $this->model->where($this->primaryKey, $id);
                  if ($this->model->update()) {
                    return $this->respond([
                      'message' => 'Item has been updated',
                    ], 200, 'OK');
                  } else {
                    return $this->respond([
                      'message' => 'Something went wrong while updating item, please try again later',
                    ], 500, 'Internal Server Error');
                  }
                } else {
                  return $this->respond([
                    'message' => 'Item with this id is not found',
                  ], 404, 'Not Found');
                }
              } catch (Exception $ex) {
                return $this->respond([
                  'message' => 'Something went wrong while updating item, please try again later',
                ], 500, 'Internal Server Error');
              }
            } else {
              return $this->respond([
                'message' => 'Parameter(s) might not fulfilled basic requirement(s)',
                'parameter' => $this->validator->getErrors(),
              ], 400, 'Bad Request');
            }
          } else {
            return $this->respond([
              'message' => 'Either detail or status must be sent as body request to action updating item',
            ], 400, 'Bad Request');
          }
          break;
        default:
          return $this->respond([
            'message' => 'This kind of method request is not accepted',
            'method' => strtoupper($this->request->getMethod()),
          ], 405, 'Method Not Allowed');
      };
    }
  }
}